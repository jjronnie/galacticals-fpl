<?php

namespace App\Jobs;

use App\Models\FplFixture;
use App\Services\SyncJobProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncFixturesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    /** @var int[] */
    public array $backoff = [60, 180, 300];

    public function handle(): void
    {
        SyncJobProgressService::start(
            SyncJobProgressService::SYNC_FIXTURES,
            1,
            'Fetching fixtures from FPL API...'
        );

        try {
            $response = $this->fplRequest()->get($this->endpoint('fixtures/'));

            if ($response->failed()) {
                throw new \RuntimeException("FPL fixtures API returned status: {$response->status()}");
            }

            $fixtures = $response->json();

            if (! is_array($fixtures)) {
                throw new \RuntimeException('Invalid response format from FPL fixtures API.');
            }

            $count = count($fixtures);

            SyncJobProgressService::progress(
                SyncJobProgressService::SYNC_FIXTURES,
                1,
                1,
                "Upserting {$count} fixtures..."
            );

            $upsertData = [];

            foreach ($fixtures as $fixture) {
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
            }

            if ($upsertData !== []) {
                $columns = [
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

                FplFixture::upsert($upsertData, ['fpl_fixture_id'], $columns);
            }

            SyncJobProgressService::complete(
                SyncJobProgressService::SYNC_FIXTURES,
                "Fixtures synced ({$count} fixtures)."
            );

            Log::info('Fixtures synced.', ['count' => $count]);
        } catch (\Throwable $exception) {
            Log::error('SyncFixturesJob failed.', [
                'exception_class' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            SyncJobProgressService::fail(
                SyncJobProgressService::SYNC_FIXTURES,
                'Fixtures sync failed.'
            );

            throw $exception;
        }
    }

    private function fplRequest(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(30)
            ->retry(3, 2000);
    }

    private function endpoint(string $path): string
    {
        $baseUrl = rtrim((string) config('services.fpl.base_url', 'https://fantasy.premierleague.com/api'), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
