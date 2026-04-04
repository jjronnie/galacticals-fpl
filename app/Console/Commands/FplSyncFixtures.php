<?php

namespace App\Console\Commands;

use App\Models\FplFixture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FplSyncFixtures extends Command
{
    protected $signature = 'fpl:sync-fixtures {--force : Skip live match check and always sync}';

    protected $description = 'Fetch FPL fixtures from the API and upsert them into the database. Syncs every 2 minutes during live matches, hourly otherwise.';

    private const SYNC_COOLDOWN_MINUTES = 2;

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->shouldSync()) {
            $this->info('No live or upcoming fixtures. Skipping sync.');

            return self::SUCCESS;
        }

        $this->info('Fetching fixtures from FPL API...');

        try {
            $response = Http::timeout(10)
                ->retry(3, 2000)
                ->get(config('services.fpl.base_url', 'https://fantasy.premierleague.com/api').'/fixtures/');

            if (! $response->successful()) {
                $this->error("FPL API returned status: {$response->status()}");
                Log::error('FPL fixtures API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return self::FAILURE;
            }

            $fixtures = $response->json();

            if (! is_array($fixtures)) {
                $this->error('Invalid response format from FPL API.');

                return self::FAILURE;
            }

            $count = 0;
            $liveCount = 0;
            $upsertData = [];

            foreach ($fixtures as $fixture) {
                $isLive = ($fixture['started'] ?? false) && ! ($fixture['finished'] ?? false);

                if ($isLive) {
                    $liveCount++;
                }

                $upsertData[] = [
                    'fpl_fixture_id' => $fixture['id'],
                    'event' => $fixture['event'],
                    'team_h' => $fixture['team_h'],
                    'team_a' => $fixture['team_a'],
                    'team_h_difficulty' => $fixture['team_h_difficulty'] ?? null,
                    'team_a_difficulty' => $fixture['team_a_difficulty'] ?? null,
                    'kickoff_time' => isset($fixture['kickoff_time']) ? date('Y-m-d H:i:s', strtotime($fixture['kickoff_time'])) : null,
                    'started' => (bool) ($fixture['started'] ?? false),
                    'finished' => (bool) ($fixture['finished'] ?? false),
                    'finished_provisional' => (bool) ($fixture['finished_provisional'] ?? false),
                    'team_h_score' => $fixture['team_h_score'],
                    'team_a_score' => $fixture['team_a_score'],
                    'minutes' => $fixture['minutes'],
                    'pulse_id' => $fixture['pulse_id'] ?? null,
                    'stats' => isset($fixture['stats']) ? json_encode($fixture['stats']) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $count++;
            }

            if ($count === 0) {
                $this->warn('No fixtures returned from API.');

                return self::SUCCESS;
            }

            $columns = [
                'fpl_fixture_id',
                'event',
                'team_h',
                'team_a',
                'team_h_difficulty',
                'team_a_difficulty',
                'kickoff_time',
                'started',
                'finished',
                'finished_provisional',
                'team_h_score',
                'team_a_score',
                'minutes',
                'pulse_id',
                'stats',
                'updated_at',
            ];

            $uniqueBy = ['fpl_fixture_id'];

            FplFixture::upsert($upsertData, $uniqueBy, $columns);

            $this->info("Upserted {$count} fixtures".($liveCount > 0 ? " ({$liveCount} live)" : '').'.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Fixture sync failed: {$e->getMessage()}");
            Log::error('FPL fixture sync exception', [
                'exception_class' => $e::class,
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }

    private function shouldSync(): bool
    {
        if (Cache::has('fpl:fixtures:syncing')) {
            return false;
        }

        $hasLiveFixtures = FplFixture::query()
            ->where('started', true)
            ->where('finished', false)
            ->exists();

        if ($hasLiveFixtures) {
            Cache::put('fpl:fixtures:syncing', true, now()->addMinutes(self::SYNC_COOLDOWN_MINUTES));

            return true;
        }

        $hasUpcomingFixtures = FplFixture::query()
            ->where('started', false)
            ->whereBetween('kickoff_time', [now(), now()->addMinutes(30)])
            ->exists();

        if ($hasUpcomingFixtures) {
            Cache::put('fpl:fixtures:syncing', true, now()->addMinutes(self::SYNC_COOLDOWN_MINUTES));

            return true;
        }

        return false;
    }
}
