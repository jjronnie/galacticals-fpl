<?php

namespace App\Jobs;

use App\Models\GameweekScore;
use App\Models\League;
use App\Models\LeagueGameweekStanding;
use App\Services\SyncJobProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ComputeLeagueGameweekStandingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 3;

    /** @var int[] */
    public array $backoff = [60, 180, 300];

    public function __construct(private readonly int $leagueId, private readonly ?int $seasonYear = null) {}

    public function handle(): void
    {
        $league = League::find($this->leagueId);

        if ($league === null) {
            Log::warning('Cannot compute gameweek standings, league not found.', [
                'league_id' => $this->leagueId,
            ]);

            SyncJobProgressService::incrementProcessed(
                SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
                true,
                'Gameweek table computation failed: league not found.'
            );

            return;
        }

        SyncJobProgressService::start(
            SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
            null,
            "Computing gameweek tables for {$league->name}..."
        );

        $seasonYear = $this->seasonYear ?? (int) ($league->season ?? now()->year);

        $managerIds = $league->managers()->pluck('managers.id');

        if ($managerIds->isEmpty()) {
            SyncJobProgressService::incrementProcessed(
                SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
                false,
                "Gameweek table computation skipped for {$league->name}: no managers."
            );

            return;
        }

        $gameweeks = GameweekScore::query()
            ->whereIn('manager_id', $managerIds)
            ->where('season_year', $seasonYear)
            ->select('gameweek')
            ->distinct()
            ->orderBy('gameweek')
            ->pluck('gameweek')
            ->map(fn ($gameweek): int => (int) $gameweek)
            ->all();

        if ($gameweeks === []) {
            SyncJobProgressService::incrementProcessed(
                SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
                false,
                "Gameweek table computation skipped for {$league->name}: no gameweek scores."
            );

            return;
        }

        $league->update([
            'sync_status' => 'processing',
            'sync_message' => 'Computing gameweek standings...',
        ]);

        foreach ($gameweeks as $index => $gameweek) {
            $scores = GameweekScore::query()
                ->whereIn('manager_id', $managerIds)
                ->where('season_year', $seasonYear)
                ->where('gameweek', $gameweek)
                ->get(['manager_id', 'points', 'total_points']);

            if ($scores->isEmpty()) {
                continue;
            }

            $averagePoints = (float) $scores->avg('points');

            $sortedScores = $scores->sortByDesc('points')->values();

            $rows = [];
            $currentRank = 0;
            $position = 0;
            $previousPoints = null;

            foreach ($sortedScores as $score) {
                $position++;

                if ($previousPoints !== $score->points) {
                    $currentRank = $position;
                    $previousPoints = $score->points;
                }

                $rows[] = [
                    'league_id' => $league->id,
                    'gameweek' => $gameweek,
                    'manager_id' => $score->manager_id,
                    'rank' => $currentRank,
                    'points' => (int) $score->points,
                    'total_points' => (int) ($score->total_points ?? 0),
                    'difference_to_average' => round((int) $score->points - $averagePoints, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            LeagueGameweekStanding::upsert(
                $rows,
                ['league_id', 'gameweek', 'manager_id'],
                ['rank', 'points', 'total_points', 'difference_to_average', 'updated_at']
            );

            $league->update([
                'sync_message' => sprintf(
                    'Computed gameweek standings %d/%d (GW %d).',
                    $index + 1,
                    count($gameweeks),
                    $gameweek
                ),
            ]);
        }

        $league->update([
            'sync_status' => 'completed',
            'sync_message' => 'Gameweek standings updated successfully.',
            'last_synced_at' => now(),
        ]);

        Cache::forget('league_stats_'.$league->id);
        Cache::forget('league_available_gameweeks_'.$league->id);

        foreach ($gameweeks as $gameweek) {
            Cache::forget("league_gameweek_standings_{$league->id}_{$gameweek}");
            Cache::forget("league_trends_{$league->id}_{$gameweek}");
        }

        SyncJobProgressService::incrementProcessed(
            SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
            false,
            "Gameweek tables computed for {$league->name}."
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ComputeLeagueGameweekStandingsJob failed permanently.', [
            'league_id' => $this->leagueId,
            'error' => $exception->getMessage(),
        ]);

        SyncJobProgressService::incrementProcessed(
            SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
            true,
            'A gameweek table computation job failed after retries.'
        );
    }
}
