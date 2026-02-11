<?php

namespace App\Jobs;

use App\Models\FplPlayer;
use App\Models\FplTeam;
use App\Models\GameweekScore;
use App\Models\Manager;
use App\Models\ManagerChip;
use App\Models\ManagerPick;
use App\Services\SyncJobProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchManagerProfilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;

    public int $tries = 3;

    /** @var int[] */
    public array $backoff = [120, 300, 600];

    /** @var array<int, array<int, int>> */
    private array $eventPointsCache = [];

    /** @var array<int, true> */
    private array $playerCatalogLookup = [];

    private bool $teamCatalogEnsured = false;

    private bool $playerCatalogLoaded = false;

    private bool $teamCatalogRefreshAttempted = false;

    /**
     * @param  array<int>|null  $managerIds
     */
    public function __construct(private readonly ?array $managerIds = null) {}

    public function handle(): void
    {
        $this->ensureTeamCatalog();
        $this->loadPlayerCatalogLookup();
        $managers = $this->managerQuery()->get();

        if ($managers->isEmpty()) {
            SyncJobProgressService::complete(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                'No claimed profiles found to sync.'
            );

            return;
        }

        /** @var Collection<int, Collection<int, Manager>> $managersByEntry */
        $managersByEntry = $managers->groupBy('entry_id');
        $totalEntries = $managersByEntry->count();
        $processedEntries = 0;
        $failedEntries = 0;
        $finishedGameweeks = $this->finishedGameweeks();

        SyncJobProgressService::start(
            SyncJobProgressService::FETCH_MANAGER_PROFILES,
            $totalEntries,
            'Starting claimed profile sync...'
        );

        foreach ($managersByEntry as $entryId => $entryManagers) {
            $processedEntries++;
            $managerIds = $entryManagers->pluck('id')->all();
            $entryIdInt = (int) $entryId;
            $currentStep = 'entry_profile';
            $currentPath = "entry/{$entryIdInt}/";
            $currentGameweek = null;

            Manager::whereIn('id', $managerIds)->update([
                'sync_status' => 'processing',
                'sync_message' => "Syncing profile data ({$processedEntries}/{$totalEntries})",
            ]);

            try {
                $profilePayload = $this->fetchJson($currentPath);
                $currentStep = 'entry_history';
                $currentPath = "entry/{$entryIdInt}/history/";
                $historyPayload = $this->fetchJson($currentPath);

                $this->syncManagerIdentity($entryManagers, $profilePayload);
                $this->syncGameweekHistory($entryManagers, $historyPayload['current'] ?? []);
                $this->syncChips($entryManagers, $historyPayload['chips'] ?? []);

                $gameweeksToSync = $this->gameweeksToSync($entryManagers, $finishedGameweeks);
                $currentStep = 'picks';

                foreach ($gameweeksToSync as $gameweek) {
                    $currentGameweek = $gameweek;
                    $currentPath = "entry/{$entryIdInt}/event/{$gameweek}/picks/";
                    $picksPayload = $this->fetchJson($currentPath);
                    $currentStep = 'event_points';
                    $currentPath = "event/{$gameweek}/live/";
                    $pointsMap = $this->eventPointsForGameweek($gameweek);
                    $currentStep = 'persist_picks';

                    foreach ($entryManagers as $entryManager) {
                        if (! $entryManager instanceof Manager) {
                            Log::warning('Skipping profile picks sync for non-manager row.', [
                                'entry_id' => $entryIdInt,
                                'gameweek' => $gameweek,
                                'row_type' => get_debug_type($entryManager),
                            ]);

                            continue;
                        }

                        $this->syncPicksForManager($entryManager, $gameweek, $picksPayload, $pointsMap);
                    }

                    $currentStep = 'picks';
                }

                Manager::whereIn('id', $managerIds)->update([
                    'sync_status' => 'completed',
                    'sync_message' => "Profile synced ({$processedEntries}/{$totalEntries})",
                    'last_synced_at' => now(),
                ]);

                $this->forgetProfileCaches($entryIdInt, $managerIds);
            } catch (\Throwable $exception) {
                $failedEntries++;
                $stepLabel = str_replace('_', ' ', $currentStep);
                $syncError = $this->truncateMessage($exception->getMessage(), 160);

                Log::warning('Failed to sync manager profile data.', [
                    'entry_id' => $entryIdInt,
                    'manager_ids' => $managerIds,
                    'step' => $currentStep,
                    'path' => $currentPath,
                    'gameweek' => $currentGameweek,
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);

                Manager::whereIn('id', $managerIds)->update([
                    'sync_status' => 'failed',
                    'sync_message' => $this->truncateMessage("Failed during {$stepLabel}: {$syncError}", 255),
                ]);
            }

            SyncJobProgressService::progress(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                $processedEntries,
                $totalEntries,
                sprintf(
                    'Synced %d/%d profile entries%s.',
                    $processedEntries,
                    $totalEntries,
                    $failedEntries > 0 ? " ({$failedEntries} failed)" : ''
                )
            );

            usleep($this->managerIntervalMicroseconds());
        }

        if ($failedEntries > 0) {
            SyncJobProgressService::fail(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                sprintf(
                    'Profile sync finished with %d failures out of %d entries.',
                    $failedEntries,
                    $totalEntries
                )
            );

            return;
        }

        SyncJobProgressService::complete(
            SyncJobProgressService::FETCH_MANAGER_PROFILES,
            sprintf('Successfully synced %d claimed profile entries.', $totalEntries)
        );
    }

    private function managerQuery()
    {
        $query = Manager::query()->with('league:id,season');

        if ($this->managerIds !== null && $this->managerIds !== []) {
            return $query->whereIn('id', $this->managerIds);
        }

        return $query->whereNotNull('user_id');
    }

    /**
     * @param  Collection<int, Manager>  $entryManagers
     */
    private function syncManagerIdentity(Collection $entryManagers, array $profilePayload): void
    {
        $fullName = trim(($profilePayload['player_first_name'] ?? '').' '.($profilePayload['player_last_name'] ?? ''));
        $favouriteTeamKey = $this->favouriteTeamPayloadKey($profilePayload);

        $attributes = [
            'player_first_name' => $profilePayload['player_first_name'] ?? null,
            'player_last_name' => $profilePayload['player_last_name'] ?? null,
            'region_name' => $profilePayload['player_region_name'] ?? null,
        ];

        if ($favouriteTeamKey !== null) {
            $attributes['favourite_team_id'] = $this->resolveFavouriteTeamId($profilePayload[$favouriteTeamKey]);
        }

        if ($fullName !== '') {
            $attributes['player_name'] = $fullName;
        }

        Manager::whereIn('id', $entryManagers->pluck('id')->all())->update($attributes);
    }

    /**
     * @param  Collection<int, Manager>  $entryManagers
     * @param  array<int, array<string, mixed>>  $history
     */
    private function syncGameweekHistory(Collection $entryManagers, array $history): void
    {
        if ($history === []) {
            return;
        }

        foreach ($entryManagers as $manager) {
            $seasonYear = (int) ($manager->league?->season ?? now()->year);

            foreach ($history as $event) {
                GameweekScore::updateOrCreate(
                    [
                        'manager_id' => $manager->id,
                        'gameweek' => (int) $event['event'],
                        'season_year' => $seasonYear,
                    ],
                    [
                        'points' => (int) ($event['points'] ?? 0),
                        'total_points' => (int) ($event['total_points'] ?? 0),
                        'overall_rank' => (int) ($event['overall_rank'] ?? 0),
                        'bank' => (int) ($event['bank'] ?? 0),
                        'value' => (int) ($event['value'] ?? 0),
                        'event_transfers' => (int) ($event['event_transfers'] ?? 0),
                        'event_transfers_cost' => (int) ($event['event_transfers_cost'] ?? 0),
                        'points_on_bench' => (int) ($event['points_on_bench'] ?? 0),
                    ]
                );
            }

            $latestTotalPoints = collect($history)->last()['total_points'] ?? null;

            if ($latestTotalPoints !== null) {
                $manager->update([
                    'total_points' => (int) $latestTotalPoints,
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, Manager>  $entryManagers
     * @param  array<int, array<string, mixed>>  $chips
     */
    private function syncChips(Collection $entryManagers, array $chips): void
    {
        if ($chips === []) {
            return;
        }

        foreach ($entryManagers as $manager) {
            foreach ($chips as $chip) {
                $gameweek = (int) ($chip['event'] ?? 0);

                if ($gameweek === 0) {
                    continue;
                }

                $pointsAfter = GameweekScore::query()
                    ->where('manager_id', $manager->id)
                    ->where('gameweek', $gameweek)
                    ->value('total_points');

                $pointsBefore = GameweekScore::query()
                    ->where('manager_id', $manager->id)
                    ->where('gameweek', '<', $gameweek)
                    ->orderByDesc('gameweek')
                    ->value('total_points');

                ManagerChip::updateOrCreate(
                    [
                        'manager_id' => $manager->id,
                        'gameweek' => $gameweek,
                        'chip_name' => (string) ($chip['name'] ?? 'unknown'),
                    ],
                    [
                        'points_before' => $pointsBefore,
                        'points_after' => $pointsAfter,
                    ]
                );
            }
        }
    }

    /**
     * @param  array<int, int>  $pointsMap
     */
    private function syncPicksForManager(Manager $manager, int $gameweek, array $payload, array $pointsMap): void
    {
        $seasonYear = (int) ($manager->league?->season ?? now()->year);
        $picks = collect($payload['picks'] ?? []);
        $missingPlayerIds = $this->missingPlayerIdsFromPicks($picks);

        if ($missingPlayerIds !== []) {
            $this->ensureTeamCatalog(forceRefresh: true);
            $missingPlayerIds = $this->missingPlayerIdsFromPicks($picks, forceReload: true);
        }

        $missingPlayerLookup = array_fill_keys($missingPlayerIds, true);

        if ($missingPlayerIds !== []) {
            Log::warning('Skipping picks with unknown player IDs during profile sync.', [
                'manager_id' => $manager->id,
                'entry_id' => (int) $manager->entry_id,
                'gameweek' => $gameweek,
                'missing_player_ids' => $missingPlayerIds,
            ]);
        }

        foreach ($picks as $pick) {
            $playerId = (int) ($pick['element'] ?? 0);

            if ($playerId === 0) {
                continue;
            }

            if (isset($missingPlayerLookup[$playerId])) {
                continue;
            }

            ManagerPick::updateOrCreate(
                [
                    'manager_id' => $manager->id,
                    'gameweek' => $gameweek,
                    'player_id' => $playerId,
                ],
                [
                    'position' => (int) ($pick['position'] ?? 0),
                    'multiplier' => (int) ($pick['multiplier'] ?? 1),
                    'is_captain' => (bool) ($pick['is_captain'] ?? false),
                    'is_vice_captain' => (bool) ($pick['is_vice_captain'] ?? false),
                    'event_points' => $pointsMap[$playerId] ?? null,
                ]
            );
        }

        $captainPick = $picks->first(fn (array $pick): bool => (bool) ($pick['is_captain'] ?? false));
        $viceCaptainPick = $picks->first(fn (array $pick): bool => (bool) ($pick['is_vice_captain'] ?? false));

        $captainPoints = null;
        $viceCaptainPoints = null;

        if ($captainPick !== null) {
            $captainPlayerPoints = $pointsMap[(int) $captainPick['element']] ?? 0;
            $captainPoints = (int) ($captainPlayerPoints * max((int) ($captainPick['multiplier'] ?? 1), 1));
        }

        if ($viceCaptainPick !== null) {
            $vicePlayerPoints = $pointsMap[(int) $viceCaptainPick['element']] ?? 0;
            $viceCaptainPoints = (int) $vicePlayerPoints;
        }

        $bestPickPoints = $picks
            ->map(fn (array $pick): int => (int) ($pointsMap[(int) ($pick['element'] ?? 0)] ?? 0))
            ->max();

        $autopSubPoints = collect($payload['automatic_subs'] ?? [])->sum(function (array $sub) use ($pointsMap): int {
            $elementIn = (int) ($sub['element_in'] ?? 0);
            $elementOut = (int) ($sub['element_out'] ?? 0);

            return (int) (($pointsMap[$elementIn] ?? 0) - ($pointsMap[$elementOut] ?? 0));
        });

        GameweekScore::updateOrCreate(
            [
                'manager_id' => $manager->id,
                'gameweek' => $gameweek,
                'season_year' => $seasonYear,
            ],
            [
                'captain_points' => $captainPoints,
                'vice_captain_points' => $viceCaptainPoints,
                'best_pick_points' => $bestPickPoints,
                'autop_sub_points' => $autopSubPoints,
            ]
        );
    }

    /**
     * @return array<int>
     */
    private function finishedGameweeks(): array
    {
        $bootstrapData = Cache::get('fpl.bootstrap-static.latest');

        if (! is_array($bootstrapData)) {
            try {
                Cache::lock('fpl.bootstrap-static.refresh.lock', 30)->block(10, function () use (&$bootstrapData): void {
                    $cachedBootstrapData = Cache::get('fpl.bootstrap-static.latest');

                    if (is_array($cachedBootstrapData)) {
                        $bootstrapData = $cachedBootstrapData;

                        return;
                    }

                    $bootstrapData = $this->fetchJson('bootstrap-static/');
                    Cache::put('fpl.bootstrap-static.latest', $bootstrapData, now()->addDay());
                });
            } catch (\Throwable $exception) {
                Log::warning('Failed to refresh bootstrap cache under lock for profile sync.', [
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);

                if (! is_array($bootstrapData)) {
                    $bootstrapData = $this->fetchJson('bootstrap-static/');
                    Cache::put('fpl.bootstrap-static.latest', $bootstrapData, now()->addDay());
                }
            }
        }

        return collect($bootstrapData['events'] ?? [])
            ->filter(fn (array $event): bool => (bool) ($event['finished'] ?? false))
            ->pluck('id')
            ->map(fn ($gameweek): int => (int) $gameweek)
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function eventPointsForGameweek(int $gameweek): array
    {
        if (array_key_exists($gameweek, $this->eventPointsCache)) {
            return $this->eventPointsCache[$gameweek];
        }

        $payload = $this->fetchJson("event/{$gameweek}/live/");

        $pointsMap = collect($payload['elements'] ?? [])->mapWithKeys(function (array $player): array {
            return [
                (int) $player['id'] => (int) ($player['stats']['total_points'] ?? 0),
            ];
        })->all();

        $this->eventPointsCache[$gameweek] = $pointsMap;

        return $pointsMap;
    }

    private function fetchJson(string $path): array
    {
        $url = $this->endpoint($path);

        try {
            $response = $this->fplRequest()->get($url);
        } catch (\Throwable $exception) {
            Log::warning('FPL API transport error during manager profile sync.', [
                'path' => $path,
                'url' => $url,
                'exception_class' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        if ($response->failed()) {
            $statusCode = $response->status();
            $bodySnippet = $this->responseBodySnippet($response->body());

            Log::warning('FPL API returned non-success response during manager profile sync.', [
                'path' => $path,
                'url' => $url,
                'status' => $statusCode,
                'body_snippet' => $bodySnippet,
            ]);

            throw new \RuntimeException("FPL API request failed for path: {$path} with status {$statusCode}");
        }

        usleep($this->pageIntervalMicroseconds());

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new \RuntimeException("Invalid JSON payload received for path: {$path}");
        }

        return $payload;
    }

    private function fplRequest(): PendingRequest
    {
        $request = Http::acceptJson()
            ->connectTimeout($this->connectTimeoutSeconds())
            ->timeout($this->requestTimeoutSeconds())
            ->retry(
                $this->requestRetryAttempts(),
                fn (int $attempt): int => $this->retryDelayMilliseconds($attempt),
                fn ($exception): bool => $this->shouldRetryException($exception),
                throw: false
            );

        if ($this->forceIpv4()) {
            $request = $request->withOptions([
                'force_ip_resolve' => 'v4',
            ]);
        }

        return $request;
    }

    private function endpoint(string $path): string
    {
        $baseUrl = rtrim((string) config('services.fpl.base_url', 'https://fantasy.premierleague.com/api'), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }

    private function managerIntervalMicroseconds(): int
    {
        $milliseconds = (int) config('services.fpl.manager_request_interval_ms', 300);

        return max($milliseconds, 0) * 1000;
    }

    private function pageIntervalMicroseconds(): int
    {
        $milliseconds = (int) config('services.fpl.page_request_interval_ms', 200);

        return max($milliseconds, 0) * 1000;
    }

    /**
     * @param  Collection<int, Manager>  $entryManagers
     * @param  array<int>  $finishedGameweeks
     * @return array<int>
     */
    private function gameweeksToSync(Collection $entryManagers, array $finishedGameweeks): array
    {
        if ($finishedGameweeks === []) {
            return [];
        }

        $managerIds = $entryManagers->pluck('id')->all();
        $expectedPickRows = max(15 * count($managerIds), 15);
        $recentGameweeks = collect($finishedGameweeks)->sortDesc()->take(3)->values()->all();

        $existingPickCounts = ManagerPick::query()
            ->whereIn('manager_id', $managerIds)
            ->whereIn('gameweek', $finishedGameweeks)
            ->selectRaw('gameweek, COUNT(*) as picks_count')
            ->groupBy('gameweek')
            ->pluck('picks_count', 'gameweek');

        return collect($finishedGameweeks)
            ->filter(function (int $gameweek) use ($existingPickCounts, $expectedPickRows, $recentGameweeks): bool {
                $pickCount = (int) ($existingPickCounts[$gameweek] ?? 0);

                if ($pickCount < $expectedPickRows) {
                    return true;
                }

                return in_array($gameweek, $recentGameweeks, true);
            })
            ->values()
            ->all();
    }

    private function ensureTeamCatalog(bool $forceRefresh = false): void
    {
        $teamCount = FplTeam::query()->count();
        $playerCount = FplPlayer::query()->count();
        $catalogReady = $teamCount >= $this->catalogMinimumTeams() && $playerCount >= $this->catalogMinimumPlayers();

        if (! $forceRefresh && $this->teamCatalogEnsured && $catalogReady) {
            return;
        }

        if (! $forceRefresh && $catalogReady) {
            $this->teamCatalogEnsured = true;
            $this->loadPlayerCatalogLookup(true);

            return;
        }

        if ($forceRefresh && $this->teamCatalogRefreshAttempted) {
            return;
        }

        $this->teamCatalogEnsured = true;

        if ($forceRefresh) {
            $this->teamCatalogRefreshAttempted = true;
        }

        try {
            FetchFplDataJob::dispatchSync();
            $this->loadPlayerCatalogLookup(true);
        } catch (\Throwable $exception) {
            Log::warning('Unable to refresh FPL teams before profile sync.', [
                'force_refresh' => $forceRefresh,
                'team_count' => $teamCount,
                'player_count' => $playerCount,
                'exception_class' => $exception::class,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function favouriteTeamPayloadKey(array $profilePayload): ?string
    {
        foreach (['favourite_team', 'favourite_team_id', 'favorite_team'] as $key) {
            if (array_key_exists($key, $profilePayload)) {
                return $key;
            }
        }

        return null;
    }

    private function resolveFavouriteTeamId(mixed $rawFavouriteTeam): ?int
    {
        if ($rawFavouriteTeam === null || $rawFavouriteTeam === '' || (string) $rawFavouriteTeam === '0') {
            return null;
        }

        if (! is_numeric($rawFavouriteTeam)) {
            return null;
        }

        $favouriteTeamId = (int) $rawFavouriteTeam;

        if ($favouriteTeamId <= 0) {
            return null;
        }

        if (FplTeam::query()->whereKey($favouriteTeamId)->exists()) {
            return $favouriteTeamId;
        }

        $teamIdByCode = FplTeam::query()
            ->where('code', $favouriteTeamId)
            ->value('id');

        if ($teamIdByCode !== null) {
            return (int) $teamIdByCode;
        }

        $this->ensureTeamCatalog();

        if (FplTeam::query()->whereKey($favouriteTeamId)->exists()) {
            return $favouriteTeamId;
        }

        $teamIdByCode = FplTeam::query()
            ->where('code', $favouriteTeamId)
            ->value('id');

        return $teamIdByCode !== null ? (int) $teamIdByCode : null;
    }

    /**
     * @param  array<int>  $managerIds
     */
    private function forgetProfileCaches(int $entryId, array $managerIds): void
    {
        foreach ($managerIds as $managerId) {
            Cache::forget('profile_stats_'.$managerId);
        }

        Cache::forget('profile_stats_entry_'.$entryId);

        foreach ([
            'overview',
            'contributions',
            'chips',
            'captaincy',
            'transfers',
            'value',
            'awards',
            'history',
        ] as $section) {
            Cache::forget("profile_stats_entry_{$entryId}_{$section}");
            Cache::forget("profile_stats_entry_v3_{$entryId}_{$section}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('FetchManagerProfilesJob failed permanently.', [
            'error' => $exception->getMessage(),
        ]);

        SyncJobProgressService::fail(
            SyncJobProgressService::FETCH_MANAGER_PROFILES,
            'Claimed profile sync failed after retries.'
        );
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $picks
     * @return array<int>
     */
    private function missingPlayerIdsFromPicks(Collection $picks, bool $forceReload = false): array
    {
        $playerIds = $picks
            ->map(fn (array $pick): int => (int) ($pick['element'] ?? 0))
            ->filter(fn (int $playerId): bool => $playerId > 0)
            ->unique()
            ->values()
            ->all();

        if ($playerIds === []) {
            return [];
        }

        $this->loadPlayerCatalogLookup($forceReload);

        return collect($playerIds)
            ->filter(fn (int $playerId): bool => ! isset($this->playerCatalogLookup[$playerId]))
            ->values()
            ->all();
    }

    private function loadPlayerCatalogLookup(bool $forceReload = false): void
    {
        if ($this->playerCatalogLoaded && ! $forceReload) {
            return;
        }

        $this->playerCatalogLookup = FplPlayer::query()
            ->pluck('id')
            ->mapWithKeys(fn ($playerId): array => [(int) $playerId => true])
            ->all();

        $this->playerCatalogLoaded = true;
    }

    private function requestRetryAttempts(): int
    {
        $attempts = (int) config('services.fpl.retry_attempts', 4);

        return max($attempts, 1);
    }

    private function retryDelayMilliseconds(int $attempt): int
    {
        $baseDelay = max((int) config('services.fpl.retry_initial_delay_ms', 750), 100);

        return min((int) ($baseDelay * (2 ** max($attempt - 1, 0))), 10000);
    }

    private function requestTimeoutSeconds(): int
    {
        $timeout = (int) config('services.fpl.request_timeout_seconds', 45);

        return max($timeout, 5);
    }

    private function connectTimeoutSeconds(): int
    {
        $timeout = (int) config('services.fpl.connect_timeout_seconds', 10);

        return max($timeout, 2);
    }

    private function forceIpv4(): bool
    {
        $value = config('services.fpl.force_ipv4', false);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL) === true;
    }

    private function shouldRetryException(mixed $exception): bool
    {
        return $exception instanceof ConnectionException
            || $exception instanceof RequestException;
    }

    private function responseBodySnippet(string $body): string
    {
        $normalizedBody = trim(preg_replace('/\s+/', ' ', $body) ?? '');

        return $this->truncateMessage($normalizedBody, 180);
    }

    private function truncateMessage(string $message, int $maxLength): string
    {
        if (mb_strlen($message) <= $maxLength) {
            return $message;
        }

        return rtrim(mb_substr($message, 0, $maxLength - 3)).'...';
    }

    private function catalogMinimumTeams(): int
    {
        $minimumTeams = (int) config('services.fpl.catalog_min_teams', 20);

        return max($minimumTeams, 1);
    }

    private function catalogMinimumPlayers(): int
    {
        $minimumPlayers = (int) config('services.fpl.catalog_min_players', 700);

        return max($minimumPlayers, 1);
    }
}
