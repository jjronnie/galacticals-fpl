<?php

namespace App\Jobs;

use App\Models\FplTeam;
use App\Models\GameweekScore;
use App\Models\Manager;
use App\Models\ManagerChip;
use App\Models\ManagerPick;
use App\Services\SyncJobProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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

    /**
     * @param  array<int>|null  $managerIds
     */
    public function __construct(private readonly ?array $managerIds = null) {}

    public function handle(): void
    {
        FetchFplDataJob::dispatchSync();

        $managers = $this->managerQuery()->get();

        if ($managers->isEmpty()) {
            SyncJobProgressService::complete(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                'No claimed profiles found to sync.'
            );

            return;
        }

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

            Manager::whereIn('id', $managerIds)->update([
                'sync_status' => 'processing',
                'sync_message' => "Syncing profile data ({$processedEntries}/{$totalEntries})",
            ]);

            try {
                $profilePayload = $this->fetchJson("entry/{$entryId}/");
                $historyPayload = $this->fetchJson("entry/{$entryId}/history/");

                $this->syncManagerIdentity($entryManagers, $profilePayload);
                $this->syncGameweekHistory($entryManagers, $historyPayload['current'] ?? []);
                $this->syncChips($entryManagers, $historyPayload['chips'] ?? []);

                foreach ($finishedGameweeks as $gameweek) {
                    $picksPayload = $this->fetchJson("entry/{$entryId}/event/{$gameweek}/picks/");
                    $pointsMap = $this->eventPointsForGameweek($gameweek);

                    foreach ($entryManagers as $manager) {
                        $this->syncPicksForManager($manager, $gameweek, $picksPayload, $pointsMap);
                    }
                }

                Manager::whereIn('id', $managerIds)->update([
                    'sync_status' => 'completed',
                    'sync_message' => "Profile synced ({$processedEntries}/{$totalEntries})",
                    'last_synced_at' => now(),
                ]);

                foreach ($managerIds as $managerId) {
                    Cache::forget('profile_stats_'.$managerId);
                }
                Cache::forget('profile_stats_entry_'.$entryId);
            } catch (\Throwable $exception) {
                $failedEntries++;

                Log::warning('Failed to sync manager profile data.', [
                    'entry_id' => $entryId,
                    'error' => $exception->getMessage(),
                ]);

                Manager::whereIn('id', $managerIds)->update([
                    'sync_status' => 'failed',
                    'sync_message' => 'Failed to sync profile data. Try again later.',
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

        return $query
            ->whereNotNull('user_id')
            ->where(function ($builder): void {
                $builder
                    ->whereNull('player_first_name')
                    ->orWhereNull('region_name')
                    ->orWhereNull('last_synced_at');
            });
    }

    /**
     * @param  Collection<int, Manager>  $entryManagers
     */
    private function syncManagerIdentity(Collection $entryManagers, array $profilePayload): void
    {
        $fullName = trim(($profilePayload['player_first_name'] ?? '').' '.($profilePayload['player_last_name'] ?? ''));

        $attributes = [
            'player_first_name' => $profilePayload['player_first_name'] ?? null,
            'player_last_name' => $profilePayload['player_last_name'] ?? null,
            'region_name' => $profilePayload['player_region_name'] ?? null,
            'favourite_team_id' => null,
        ];

        if (! empty($profilePayload['favourite_team'])) {
            $favouriteTeamId = (int) $profilePayload['favourite_team'];
            $attributes['favourite_team_id'] = FplTeam::query()->where('id', $favouriteTeamId)->exists()
                ? $favouriteTeamId
                : null;
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

        foreach ($picks as $pick) {
            $playerId = (int) ($pick['element'] ?? 0);

            if ($playerId === 0) {
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
            $bootstrapData = $this->fetchJson('bootstrap-static/');
            Cache::put('fpl.bootstrap-static.latest', $bootstrapData, now()->addDay());
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
        $response = Http::timeout(30)->get($this->endpoint($path));

        if ($response->failed()) {
            throw new \RuntimeException("FPL API request failed for path: {$path}");
        }

        usleep($this->pageIntervalMicroseconds());

        return $response->json();
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
}
