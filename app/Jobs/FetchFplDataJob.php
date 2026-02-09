<?php

namespace App\Jobs;

use App\Models\FplPlayer;
use App\Models\FplTeam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
        $cacheKey = 'fpl.bootstrap-static.'.now()->toDateString();

        try {
            $payload = Cache::remember($cacheKey, now()->addDay(), function (): array {
                $response = Http::timeout(30)->get($this->endpoint('bootstrap-static/'));

                if ($response->failed()) {
                    throw new \RuntimeException('Failed to fetch bootstrap-static payload from FPL API.');
                }

                return $response->json();
            });

            $teams = collect($payload['teams'] ?? [])->map(function (array $team): array {
                return [
                    'id' => $team['id'],
                    'name' => $team['name'],
                    'short_name' => $team['short_name'],
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
                    'region' => $player['region'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->values()->all();

            FplTeam::upsert(
                $teams,
                ['id'],
                ['name', 'short_name', 'code', 'strength_overall', 'updated_at']
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
                    'region',
                    'updated_at',
                ]
            );

            Cache::put('fpl.bootstrap-static.latest', $payload, now()->addDay());
            Cache::put('fpl.bootstrap-static.last_synced_at', now()->toIso8601String(), now()->addDay());

            Log::info('FPL static data synced.', [
                'teams' => count($teams),
                'players' => count($players),
            ]);
        } catch (\Throwable $exception) {
            Log::error('FetchFplDataJob failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function endpoint(string $path): string
    {
        $baseUrl = rtrim((string) config('services.fpl.base_url', 'https://fantasy.premierleague.com/api'), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
