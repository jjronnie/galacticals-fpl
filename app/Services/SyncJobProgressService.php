<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SyncJobProgressService
{
    public const FETCH_FPL_DATA = 'fetch_fpl_data';

    public const FETCH_MANAGER_PROFILES = 'fetch_manager_profiles';

    public const FETCH_LEAGUE_STANDINGS = 'fetch_league_standings';

    public const COMPUTE_GAMEWEEK_TABLES = 'compute_gameweek_tables';

    public const SEND_LEAGUE_REMINDERS = 'send_league_reminders';

    public const SYNC_FIXTURES = 'sync_fixtures';

    private const CACHE_TTL_HOURS = 24;

    /**
     * @return array<string, string>
     */
    private static function labels(): array
    {
        return [
            self::FETCH_FPL_DATA => 'Sync FPL Teams & Players',
            self::FETCH_MANAGER_PROFILES => 'Sync Claimed Profiles',
            self::FETCH_LEAGUE_STANDINGS => 'Sync League Standings',
            self::COMPUTE_GAMEWEEK_TABLES => 'Compute Gameweek Tables',
            self::SEND_LEAGUE_REMINDERS => 'Send League Reminders',
            self::SYNC_FIXTURES => 'Sync Fixtures',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return collect(array_keys(self::labels()))
            ->map(fn (string $jobKey): array => self::get($jobKey))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(string $jobKey): array
    {
        $raw = Cache::get(self::cacheKey($jobKey));

        if (! is_array($raw)) {
            return self::normalize(self::defaultState($jobKey), $jobKey);
        }

        return self::normalize($raw, $jobKey);
    }

    public static function queue(string $jobKey, int $total, string $message, bool $reset = true): void
    {
        self::mutate($jobKey, function (array $state) use ($total, $message, $reset): array {
            if ($reset) {
                $state['processed'] = 0;
                $state['failed'] = 0;
                $state['total'] = max($total, 1);
            } else {
                $state['total'] = max(((int) ($state['total'] ?? 0)) + max($total, 1), 1);
            }

            $state['status'] = 'queued';
            $state['message'] = $message;

            return $state;
        });
    }

    public static function start(string $jobKey, ?int $total = null, ?string $message = null): void
    {
        self::mutate($jobKey, function (array $state) use ($total, $message): array {
            if ($total !== null) {
                $state['total'] = max($total, 1);
            }

            $state['status'] = 'processing';

            if ($message !== null) {
                $state['message'] = $message;
            }

            return $state;
        });
    }

    public static function progress(string $jobKey, int $processed, ?int $total = null, ?string $message = null): void
    {
        self::mutate($jobKey, function (array $state) use ($processed, $total, $message): array {
            $state['status'] = 'processing';
            $state['processed'] = max($processed, 0);

            if ($total !== null) {
                $state['total'] = max($total, 1);
            }

            if ($message !== null) {
                $state['message'] = $message;
            }

            return $state;
        });
    }

    public static function complete(string $jobKey, string $message): void
    {
        self::mutate($jobKey, function (array $state) use ($message): array {
            $state['status'] = 'completed';

            if ((int) ($state['total'] ?? 0) > 0) {
                $state['processed'] = (int) $state['total'];
            }

            $state['message'] = $message;

            return $state;
        });
    }

    public static function fail(string $jobKey, string $message): void
    {
        self::mutate($jobKey, function (array $state) use ($message): array {
            $state['status'] = 'failed';
            $state['message'] = $message;

            return $state;
        });
    }

    public static function incrementProcessed(string $jobKey, bool $failed = false, ?string $message = null): void
    {
        self::mutate($jobKey, function (array $state) use ($failed, $message): array {
            $state['status'] = 'processing';
            $state['processed'] = max(((int) ($state['processed'] ?? 0)) + 1, 0);

            if ((int) ($state['total'] ?? 0) < 1) {
                $state['total'] = max((int) ($state['processed'] ?? 0), 1);
            }

            if ($failed) {
                $state['failed'] = max(((int) ($state['failed'] ?? 0)) + 1, 0);
            }

            if ($message !== null) {
                $state['message'] = $message;
            }

            $processed = (int) ($state['processed'] ?? 0);
            $total = (int) ($state['total'] ?? 0);
            $failedCount = (int) ($state['failed'] ?? 0);

            if ($total > 0 && $processed >= $total) {
                $state['status'] = $failedCount > 0 ? 'failed' : 'completed';

                if ($message === null) {
                    $state['message'] = $failedCount > 0
                        ? 'Completed with some failures.'
                        : 'Completed successfully.';
                }
            }

            return $state;
        });
    }

    private static function mutate(string $jobKey, callable $callback): void
    {
        $cacheKey = self::cacheKey($jobKey);
        $lockKey = $cacheKey.':lock';

        try {
            Cache::lock($lockKey, 5)->block(2, function () use ($cacheKey, $jobKey, $callback): void {
                $state = self::get($jobKey);
                $nextState = $callback($state);
                $nextState['updated_at'] = now()->toIso8601String();
                $normalized = self::normalize($nextState, $jobKey);

                Cache::put($cacheKey, $normalized, now()->addHours(self::CACHE_TTL_HOURS));
            });
        } catch (\Throwable) {
            $state = self::get($jobKey);
            $nextState = $callback($state);
            $nextState['updated_at'] = now()->toIso8601String();
            $normalized = self::normalize($nextState, $jobKey);

            Cache::put($cacheKey, $normalized, now()->addHours(self::CACHE_TTL_HOURS));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalize(array $state, string $jobKey): array
    {
        $total = max((int) ($state['total'] ?? 0), 0);
        $processed = max((int) ($state['processed'] ?? 0), 0);
        $displayProcessed = $total > 0 ? min($processed, $total) : $processed;
        $status = (string) ($state['status'] ?? 'idle');
        $progress = $total > 0 ? (int) round($displayProcessed / $total * 100) : 0;

        if ($status === 'completed') {
            $progress = 100;
        }

        return [
            'key' => $jobKey,
            'label' => self::labels()[$jobKey] ?? $jobKey,
            'status' => $status,
            'processed' => $displayProcessed,
            'total' => $total,
            'failed' => max((int) ($state['failed'] ?? 0), 0),
            'progress' => $progress,
            'message' => (string) ($state['message'] ?? 'Idle'),
            'updated_at' => $state['updated_at'] ?? now()->toIso8601String(),
        ];
    }

    private static function cacheKey(string $jobKey): string
    {
        return 'sync_job_progress:'.$jobKey;
    }

    /**
     * @return array<string, mixed>
     */
    private static function defaultState(string $jobKey): array
    {
        return [
            'key' => $jobKey,
            'label' => self::labels()[$jobKey] ?? $jobKey,
            'status' => 'idle',
            'processed' => 0,
            'total' => 0,
            'failed' => 0,
            'progress' => 0,
            'message' => 'Idle',
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
