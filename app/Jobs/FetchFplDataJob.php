<?php

namespace App\Jobs;

use App\Models\FplPlayer;
use App\Models\FplTeam;
use App\Services\SyncJobProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchFplDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 3;

    /** @var int[] */
    public array $backoff = [60, 180, 300];

    public function handle(): void
    {
        SyncJobProgressService::start(
            SyncJobProgressService::FETCH_FPL_DATA,
            3,
            'Fetching FPL bootstrap data...'
        );

        $cacheKey = 'fpl.bootstrap-static.'.now()->toDateString();

        try {
            $payload = Cache::get($cacheKey);

            if (! is_array($payload)) {
                Cache::lock('fpl.bootstrap-static.refresh.lock', 30)->block(10, function () use (&$payload, $cacheKey): void {
                    $cachedPayload = Cache::get($cacheKey);

                    if (is_array($cachedPayload)) {
                        $payload = $cachedPayload;

                        return;
                    }

                    $payload = $this->fetchBootstrapPayload();
                    Cache::put($cacheKey, $payload, now()->addDay());
                });
            }

            if (! is_array($payload)) {
                throw new \RuntimeException('Failed to resolve bootstrap-static payload from cache.');
            }

            if (! is_array($payload['teams'] ?? null) || ! is_array($payload['elements'] ?? null)) {
                throw new \RuntimeException('Bootstrap-static payload is missing required teams or elements arrays.');
            }

            SyncJobProgressService::progress(
                SyncJobProgressService::FETCH_FPL_DATA,
                1,
                3,
                'Bootstrap payload fetched. Syncing teams...'
            );

            $teams = collect($payload['teams'] ?? [])->map(function (array $team): array {
                return [
                    'id' => $team['id'],
                    'name' => $team['name'],
                    'short_name' => $team['short_name'],
                    'fpl_code' => $team['code'] ?? null,
                    'code' => $team['code'] ?? null,
                    'strength_overall' => $team['strength_overall_home'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->values()->all();

            $players = collect($payload['elements'] ?? [])->map(function (array $player): array {
                return [
                    'id' => $player['id'],
                    'team_id' => $player['team'],
                    'first_name' => $player['first_name'],
                    'second_name' => $player['second_name'],
                    'web_name' => $player['web_name'],
                    'element_type' => $player['element_type'],
                    'now_cost' => $player['now_cost'] ?? 0,
                    'total_points' => $player['total_points'] ?? 0,
                    'selected_by_percent' => (float) ($player['selected_by_percent'] ?? 0),
                    'form' => (float) ($player['form'] ?? 0),
                    'fpl_photo' => $player['photo'] ?? null,
                    'region' => $player['region'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->values()->all();

            FplTeam::upsert(
                $teams,
                ['id'],
                ['name', 'short_name', 'fpl_code', 'code', 'strength_overall', 'updated_at']
            );

            SyncJobProgressService::progress(
                SyncJobProgressService::FETCH_FPL_DATA,
                2,
                3,
                'Teams synced. Syncing players...'
            );

            FplPlayer::upsert(
                $players,
                ['id'],
                [
                    'team_id',
                    'first_name',
                    'second_name',
                    'web_name',
                    'element_type',
                    'now_cost',
                    'total_points',
                    'selected_by_percent',
                    'form',
                    'fpl_photo',
                    'region',
                    'updated_at',
                ]
            );

            Cache::put('fpl.bootstrap-static.latest', $payload, now()->addDay());
            Cache::put('fpl.bootstrap-static.last_synced_at', now()->toIso8601String(), now()->addDay());

            SyncJobProgressService::complete(
                SyncJobProgressService::FETCH_FPL_DATA,
                sprintf('FPL data synced (%d teams, %d players).', count($teams), count($players))
            );

            Log::info('FPL static data synced.', [
                'teams' => count($teams),
                'players' => count($players),
            ]);
        } catch (\Throwable $exception) {
            Log::error('FetchFplDataJob failed.', [
                'exception_class' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            SyncJobProgressService::fail(
                SyncJobProgressService::FETCH_FPL_DATA,
                'FPL teams/players sync failed.'
            );

            throw $exception;
        }
    }

    private function fetchBootstrapPayload(): array
    {
        $path = 'bootstrap-static/';
        $url = $this->endpoint($path);

        try {
            $response = $this->fplRequest()->get($url);
        } catch (\Throwable $exception) {
            Log::warning('FPL bootstrap request failed with transport error.', [
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

            Log::warning('FPL bootstrap request returned non-success response.', [
                'path' => $path,
                'url' => $url,
                'status' => $statusCode,
                'body_snippet' => $bodySnippet,
            ]);

            throw new \RuntimeException("Failed to fetch bootstrap-static payload from FPL API. Status: {$statusCode}");
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new \RuntimeException('Invalid bootstrap-static JSON payload received from FPL API.');
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
}
