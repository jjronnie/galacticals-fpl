<?php

namespace App\Jobs;

use App\Models\Manager;
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

    /**
     * @param  array<int>|null  $managerIds
     */
    public function __construct(private readonly ?array $managerIds = null) {}

    public function handle(): void
    {
        $managers = $this->managerQuery()->get();

        if ($managers->isEmpty()) {
            SyncJobProgressService::complete(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                'No manager profiles found to sync.'
            );

            return;
        }

        /** @var Collection<int, Collection<int, Manager>> $managersByEntry */
        $managersByEntry = $managers->groupBy('entry_id');
        $totalEntries = $managersByEntry->count();

        SyncJobProgressService::start(
            SyncJobProgressService::FETCH_MANAGER_PROFILES,
            $totalEntries,
            'Starting manager profile sync...'
        );

        // Pre-cache live event points for all finished/current gameweeks
        $this->preCacheEventPoints();

        // Order: claimed profiles first, then oldest-synced
        $orderedEntries = $managersByEntry
            ->sortBy(fn ($entryManagers) => $entryManagers->first()->claimed_at !== null ? 0 : 1)
            ->sortBy(fn ($entryManagers) => $entryManagers->first()->last_synced_at ?? '1970-01-01');

        $batchSize = $this->profileBatchSize();
        $cooldownSeconds = $this->profileCooldownSeconds();
        $processedEntries = 0;
        $failedEntries = 0;

        foreach ($orderedEntries as $entryId => $entryManagers) {
            $entryIdInt = (int) $entryId;
            $entryManagerIds = $entryManagers->pluck('id')->all();

            if ($entryIdInt <= 0 || $entryManagerIds === []) {
                $processedEntries++;

                continue;
            }

            try {
                (new FetchSingleManagerProfileJob($entryIdInt, $entryManagerIds))->handle();
            } catch (\Throwable $exception) {
                $failedEntries++;

                Log::warning('FetchManagerProfilesJob: entry sync failed, continuing with next.', [
                    'entry_id' => $entryIdInt,
                    'manager_ids' => $entryManagerIds,
                    'exception_class' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);

                Manager::whereIn('id', $entryManagerIds)->update([
                    'sync_status' => 'failed',
                    'sync_message' => 'Failed: '.$exception->getMessage(),
                ]);
            }

            $processedEntries++;

            usleep($this->managerIntervalMicroseconds());

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

            // Batch cooldown: pause every N entries to avoid overwhelming FPL API
            if ($batchSize > 0 && $processedEntries % $batchSize === 0 && $processedEntries < $totalEntries) {
                Log::info('Profile sync batch cooldown.', [
                    'processed' => $processedEntries,
                    'total' => $totalEntries,
                    'cooldown_seconds' => $cooldownSeconds,
                ]);

                sleep($cooldownSeconds);
            }
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
            sprintf('Successfully synced %d manager profile entries.', $totalEntries)
        );
    }

    private function managerQuery()
    {
        $query = Manager::query()->with('league:id,season');

        if ($this->managerIds !== null && $this->managerIds !== []) {
            return $query->whereIn('id', $this->managerIds);
        }

        return $query;
    }

    /**
     * Pre-cache event points for all finished/current gameweeks.
     * This avoids each sub-job needing to fetch event/{gw}/live/ independently.
     * With caching, each manager only makes ~5 API calls instead of ~78.
     */
    private function preCacheEventPoints(): void
    {
        $bootstrapData = Cache::get('fpl.bootstrap-static.latest');

        if (! is_array($bootstrapData)) {
            return;
        }

        $gameweeks = collect($bootstrapData['events'] ?? [])
            ->filter(fn (array $event): bool => (bool) ($event['finished'] ?? false) || (bool) ($event['is_current'] ?? false))
            ->pluck('id')
            ->map(fn ($gw): int => (int) $gw)
            ->values()
            ->all();

        $baseUrl = rtrim((string) config('services.fpl.base_url', 'https://fantasy.premierleague.com/api'), '/');
        $connectTimeout = max((int) config('services.fpl.connect_timeout_seconds', 10), 2);
        $requestTimeout = max((int) config('services.fpl.request_timeout_seconds', 45), 5);

        foreach ($gameweeks as $gameweek) {
            $cacheKey = "fpl.event_points.gw{$gameweek}";

            if (Cache::has($cacheKey)) {
                continue;
            }

            try {
                $response = Http::acceptJson()
                    ->connectTimeout($connectTimeout)
                    ->timeout($requestTimeout)
                    ->get("{$baseUrl}/event/{$gameweek}/live/");

                if ($response->failed()) {
                    continue;
                }

                $payload = $response->json();

                if (! is_array($payload)) {
                    continue;
                }

                $pointsMap = collect($payload['elements'] ?? [])->mapWithKeys(function (array $player): array {
                    return [(int) $player['id'] => (int) ($player['stats']['total_points'] ?? 0)];
                })->all();

                Cache::put($cacheKey, $pointsMap, now()->addHours(6));

                usleep(300000); // 300ms between pre-cache fetches
            } catch (\Throwable) {
                // Skip failed pre-cache; individual jobs will fetch on demand
            }
        }
    }

    private function managerIntervalMicroseconds(): int
    {
        $milliseconds = (int) config('services.fpl.manager_request_interval_ms', 300);

        return max($milliseconds, 0) * 1000;
    }

    private function profileBatchSize(): int
    {
        return max((int) config('services.fpl.profile_batch_size', 50), 1);
    }

    private function profileCooldownSeconds(): int
    {
        return max((int) config('services.fpl.profile_cooldown_seconds', 60), 0);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('FetchManagerProfilesJob (dispatcher) failed permanently.', [
            'error' => $exception->getMessage(),
        ]);

        SyncJobProgressService::fail(
            SyncJobProgressService::FETCH_MANAGER_PROFILES,
            'Manager profile sync dispatch failed after retries.'
        );
    }
}
