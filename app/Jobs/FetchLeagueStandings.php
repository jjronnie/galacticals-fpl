<?php

namespace App\Jobs;

use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use App\Services\LeagueStatsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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

    public function __construct(private readonly int $leagueId) {}

    public function handle(): void
    {
        $league = League::find($this->leagueId);

        if ($league === null) {
            Log::error('League not found for standings sync.', ['league_id' => $this->leagueId]);

            return;
        }

        try {
            $allStandings = $this->fetchAllStandings($league);

            if ($allStandings === []) {
                $league->update([
                    'sync_status' => 'failed',
                    'sync_message' => 'No managers found in this league. The league may be empty or invalid.',
                ]);

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

            ComputeLeagueGameweekStandingsJob::dispatch($league->id, (int) ($league->season ?? now()->year));

            app(LeagueStatsService::class)->flushLeagueStats($league);
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
            $response = Http::timeout(15)->get($this->endpoint("leagues-classic/{$league->league_id}/standings/"), [
                'page_standings' => $page,
            ]);

            if ($response->failed()) {
                Log::warning('Failed to fetch league standings page.', [
                    'league_id' => $league->league_id,
                    'page' => $page,
                ]);

                break;
            }

            $data = $response->json();

            if (! isset($data['league'], $data['standings']['results'])) {
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
            $manager = Manager::updateOrCreate(
                [
                    'league_id' => $league->id,
                    'entry_id' => (int) $entry['entry'],
                ],
                [
                    'player_name' => (string) $entry['player_name'],
                    'team_name' => (string) $entry['entry_name'],
                    'rank' => (int) ($entry['rank'] ?? 0),
                    'total_points' => (int) ($entry['total'] ?? 0),
                ]
            );

            $historyResponse = Http::timeout(20)->get($this->endpoint("entry/{$entry['entry']}/history/"));

            if ($historyResponse->failed()) {
                return;
            }

            $historyData = $historyResponse->json();

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
            $response = Http::timeout(15)->get($this->endpoint('bootstrap-static/'));

            if ($response->failed()) {
                return 0;
            }

            $current = collect($response->json('events', []))
                ->filter(fn (array $event): bool => (bool) ($event['finished'] ?? false))
                ->max('id');

            return (int) ($current ?? 0);
        } catch (\Throwable) {
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
    }
}
