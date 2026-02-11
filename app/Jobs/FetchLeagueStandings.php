<?php

namespace App\Jobs;

use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use App\Services\LeagueStatsService;
use App\Services\SyncJobProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchLeagueStandings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 3;

    /** @var int[] */
    public array $backoff = [120, 300, 600];

    public function __construct(
        private readonly int $leagueId,
        private readonly bool $dispatchGameweekComputation = true
    ) {}

    public function handle(): void
    {
        $league = League::find($this->leagueId);

        if ($league === null) {
            Log::error('League not found for standings sync.', ['league_id' => $this->leagueId]);

            SyncJobProgressService::incrementProcessed(
                SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
                true,
                'League sync failed: league record not found.'
            );

            return;
        }

        SyncJobProgressService::start(
            SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
            null,
            "Syncing league standings for {$league->name}..."
        );

        try {
            $allStandings = $this->fetchAllStandings($league);

            if ($allStandings === []) {
                $league->update([
                    'sync_status' => 'failed',
                    'sync_message' => 'No managers found in this league. The league may be empty or invalid.',
                ]);

                SyncJobProgressService::incrementProcessed(
                    SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
                    true,
                    "League sync failed for {$league->name}: no managers found."
                );

                return;
            }

            $totalManagers = count($allStandings);

            $league->update([
                'sync_status' => 'processing',
                'total_managers' => $totalManagers,
                'synced_managers' => 0,
                'sync_message' => "Processing {$totalManagers} managers...",
                'current_gameweek' => $this->fetchCurrentGameweek(),
            ]);

            $existingEntryIds = [];
            $processedCount = 0;

            foreach ($allStandings as $entry) {
                $this->processManager($league, $entry);
                $existingEntryIds[] = (int) $entry['entry'];
                $processedCount++;

                if ($processedCount % 5 === 0 || $processedCount === $totalManagers) {
                    $league->update([
                        'synced_managers' => $processedCount,
                        'sync_message' => "Processed {$processedCount} of {$totalManagers} managers...",
                    ]);
                }

                usleep($this->managerIntervalMicroseconds());
            }

            $managersToDelete = Manager::query()
                ->where('league_id', $league->id)
                ->whereNotIn('entry_id', $existingEntryIds)
                ->get();

            foreach ($managersToDelete as $manager) {
                GameweekScore::where('manager_id', $manager->id)->delete();
                $manager->delete();
            }

            $league->update([
                'sync_status' => 'completed',
                'sync_message' => "Successfully imported {$totalManagers} managers.",
                'synced_managers' => $totalManagers,
                'last_synced_at' => now(),
            ]);

            if ($this->dispatchGameweekComputation) {
                ComputeLeagueGameweekStandingsJob::dispatch($league->id, (int) ($league->season ?? now()->year));
            }

            app(LeagueStatsService::class)->flushLeagueStats($league);

            SyncJobProgressService::incrementProcessed(
                SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
                false,
                "League standings synced for {$league->name}."
            );
        } catch (\Throwable $exception) {
            Log::error('FetchLeagueStandings failed.', [
                'league_id' => $this->leagueId,
                'error' => $exception->getMessage(),
            ]);

            $league->update([
                'sync_status' => 'failed',
                'sync_message' => 'Failed to fetch league data. Please try again.',
            ]);

            throw $exception;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchAllStandings(League $league): array
    {
        $page = 1;
        $allStandings = [];
        $maxPages = 100;

        do {
            $path = "leagues-classic/{$league->league_id}/standings/";
            $url = $this->endpoint($path);

            try {
                $response = $this->fplRequest()->get($url, [
                    'page_standings' => $page,
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Transport error while fetching league standings page.', [
                    'league_id' => $league->league_id,
                    'path' => $path,
                    'url' => $url,
                    'page' => $page,
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);

                if ($page === 1) {
                    throw $exception;
                }

                break;
            }

            if ($response->failed()) {
                $statusCode = $response->status();
                $bodySnippet = $this->responseBodySnippet($response->body());

                Log::warning('FPL API returned non-success for league standings page.', [
                    'league_id' => $league->league_id,
                    'path' => $path,
                    'url' => $url,
                    'page' => $page,
                    'status' => $statusCode,
                    'body_snippet' => $bodySnippet,
                ]);

                if ($page === 1) {
                    throw new \RuntimeException(
                        "Failed to fetch standings for league {$league->league_id} (status {$statusCode})."
                    );
                }

                break;
            }

            $data = $response->json();

            if (! is_array($data) || ! isset($data['league'], $data['standings']['results'])) {
                Log::warning('Invalid league standings payload received.', [
                    'league_id' => $league->league_id,
                    'path' => $path,
                    'url' => $url,
                    'page' => $page,
                ]);

                if ($page === 1) {
                    throw new \RuntimeException(
                        "Invalid standings payload for league {$league->league_id} page {$page}."
                    );
                }

                break;
            }

            if ($page === 1) {
                $league->update([
                    'name' => $data['league']['name'],
                    'admin_name' => $data['league']['admin_entry'] ?? null,
                ]);
            }

            $standings = $data['standings']['results'] ?? [];
            $allStandings = array_merge($allStandings, $standings);

            $hasNext = (bool) ($data['standings']['has_next'] ?? false);
            $page++;

            usleep($this->pageIntervalMicroseconds());
        } while ($hasNext && $page <= $maxPages);

        return $allStandings;
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function processManager(League $league, array $entry): void
    {
        try {
            $entryId = (int) ($entry['entry'] ?? 0);

            if ($entryId <= 0) {
                return;
            }

            $manager = Manager::updateOrCreate(
                [
                    'league_id' => $league->id,
                    'entry_id' => $entryId,
                ],
                [
                    'player_name' => (string) $entry['player_name'],
                    'team_name' => (string) $entry['entry_name'],
                    'rank' => (int) ($entry['rank'] ?? 0),
                    'total_points' => (int) ($entry['total'] ?? 0),
                ]
            );

            $path = "entry/{$entryId}/history/";
            $url = $this->endpoint($path);

            try {
                $historyResponse = $this->fplRequest()->get($url);
            } catch (\Throwable $exception) {
                Log::warning('Transport error while fetching manager history during standings sync.', [
                    'league_id' => $league->id,
                    'entry_id' => $entryId,
                    'path' => $path,
                    'url' => $url,
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);

                return;
            }

            if ($historyResponse->failed()) {
                $statusCode = $historyResponse->status();
                $bodySnippet = $this->responseBodySnippet($historyResponse->body());

                Log::warning('FPL API returned non-success for manager history during standings sync.', [
                    'league_id' => $league->id,
                    'entry_id' => $entryId,
                    'path' => $path,
                    'url' => $url,
                    'status' => $statusCode,
                    'body_snippet' => $bodySnippet,
                ]);

                return;
            }

            $historyData = $historyResponse->json();

            if (! is_array($historyData)) {
                Log::warning('Invalid manager history payload received during standings sync.', [
                    'league_id' => $league->id,
                    'entry_id' => $entryId,
                    'path' => $path,
                    'url' => $url,
                ]);

                return;
            }

            foreach (($historyData['current'] ?? []) as $week) {
                GameweekScore::updateOrCreate(
                    [
                        'manager_id' => $manager->id,
                        'gameweek' => (int) $week['event'],
                        'season_year' => (int) ($league->season ?? now()->year),
                    ],
                    [
                        'points' => (int) ($week['points'] ?? 0),
                        'total_points' => (int) ($week['total_points'] ?? 0),
                        'overall_rank' => (int) ($week['overall_rank'] ?? 0),
                        'bank' => (int) ($week['bank'] ?? 0),
                        'value' => (int) ($week['value'] ?? 0),
                        'event_transfers' => (int) ($week['event_transfers'] ?? 0),
                        'event_transfers_cost' => (int) ($week['event_transfers_cost'] ?? 0),
                        'points_on_bench' => (int) ($week['points_on_bench'] ?? 0),
                    ]
                );
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to process manager during standings sync.', [
                'league_id' => $league->id,
                'entry_id' => $entry['entry'] ?? null,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function fetchCurrentGameweek(): int
    {
        try {
            $path = 'bootstrap-static/';
            $url = $this->endpoint($path);
            $response = $this->fplRequest()->get($url);

            if ($response->failed()) {
                Log::warning('FPL API returned non-success while fetching current gameweek.', [
                    'path' => $path,
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return 0;
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                return 0;
            }

            $current = collect($payload['events'] ?? [])
                ->filter(fn (array $event): bool => (bool) ($event['finished'] ?? false))
                ->max('id');

            return (int) ($current ?? 0);
        } catch (\Throwable $exception) {
            Log::warning('Transport error while fetching current gameweek.', [
                'league_id' => $this->leagueId,
                'exception_class' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            return 0;
        }
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

        if (mb_strlen($normalizedBody) <= 180) {
            return $normalizedBody;
        }

        return rtrim(mb_substr($normalizedBody, 0, 177)).'...';
    }

    public function failed(\Throwable $exception): void
    {
        $league = League::find($this->leagueId);

        if ($league !== null) {
            $league->update([
                'sync_status' => 'failed',
                'sync_message' => 'Failed to import league data after multiple attempts.',
            ]);
        }

        Log::error('FetchLeagueStandings failed permanently.', [
            'league_id' => $this->leagueId,
            'error' => $exception->getMessage(),
        ]);

        SyncJobProgressService::incrementProcessed(
            SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
            true,
            'A league standings sync job failed after retries.'
        );
    }
}
