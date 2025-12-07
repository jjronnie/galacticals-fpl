<?php

namespace App\Services;

use App\Models\League;
use Illuminate\Support\Facades\Cache;

class LeagueStatsService
{
    /**
     * Calculate and retrieve league statistics with caching.
     * Cache duration: 5 days.
     */
    public function getLeagueStats(League $league)
    {
        $cacheKey = $this->getCacheKey($league->id);

        return Cache::remember($cacheKey, now()->addHours(2), function () use ($league) {

            


            // Eager load necessary relationships for calculation
            // We reload the league here to ensure relationships exist inside the cache closure
            $league->load([
                'managers',
                'gameweekScores' => function ($query) use ($league) {
                    $query->where('season_year', $league->season)
                        ->orderBy('gameweek');
                }
            ]);

            $allScores = $league->gameweekScores;
            $managers = $league->managers;

            // Handle Empty Data
            if ($allScores->isEmpty()) {
                return [
                    'isEmpty' => true,
                    'standings' => collect(),
                    'gwPerformance' => collect(),
                    'stats' => $this->getEmptyStats(),
                ];
            }

            // 1. Calculate Initial Standings
            $standings = $managers->map(function ($manager) {
                return [
                    'name' => $manager->player_name,
                    'team' => $manager->team_name,
                    'total_points' => $manager->total_points,
                ];
            })->sortByDesc('total_points')->values();

            // 2. League Zone Logic
            $topManagers = $standings->take(5);
            $relegationManagers = $standings->slice(-3)->values();

            $leagueZones = [
                'champions_league' => $topManagers->take(4)->pluck('name')->all(),
                'europa_league' => $topManagers->slice(4, 1)->pluck('name')->all(),
                'relegation_zone' => $relegationManagers->pluck('name')->all(),
            ];

            // 3. Initialize Loop Variables
            $gameweeks = $allScores->groupBy('gameweek');
            $managerTotalPoints = $managers->pluck('id', 'player_name')->map(fn() => 0)->toArray();

            // Streak Tracking
            $currentTopStreak = 0;
            $currentTopManager = null;
            $longestTopStreak = 0;
            $longestStreakManager = null;
            $longestStreakStartGW = null;
            $streakStartGW = null;

            // Stat Containers
            $theBlowout = ['difference' => 0, 'gw' => null];
            $gwPerformance = [];
            $managerLeads = [];
            $managerLasts = [];
            $highestGwScore = ['manager' => null, 'points' => 0, 'gw' => null];
            $lowestGwScore = ['manager' => null, 'points' => 9999, 'gw' => null];

            // 4. Main Calculation Loop
            foreach ($gameweeks as $gw => $scores) {
                if ($scores->isEmpty() || $gw == 0)
                    continue;

                // --- Best/Worst Calculations ---
                $bestScore = $scores->max('points');
                $worstScore = $scores->min('points');
                $scoreDifference = $bestScore - $worstScore;

                // We filter the collection memory instead of querying DB again
                $bestManagers = $scores->where('points', $bestScore)->pluck('manager.player_name')->unique()->values()->all();
                $worstManagers = $scores->where('points', $worstScore)->pluck('manager.player_name')->unique()->values()->all();

                // Check for The Blowout
                if ($scoreDifference > $theBlowout['difference']) {
                    $theBlowout = [
                        'difference' => $scoreDifference,
                        'gw' => $gw,
                        'highest_scorer' => $bestManagers[0] ?? null,
                        'lowest_scorer' => $worstManagers[0] ?? null,
                        'highest_points' => $bestScore,
                        'lowest_points' => $worstScore,
                    ];
                }

                $gwPerformance[] = [
                    'gameweek' => $gw,
                    'best_managers' => $bestManagers,
                    'best_points' => $bestScore,
                    'worst_managers' => $worstManagers,
                    'worst_points' => $worstScore,
                ];

                // Update Counters
                foreach ($bestManagers as $managerName) {
                    $managerLeads[$managerName] = ($managerLeads[$managerName] ?? 0) + 1;
                }
                foreach ($worstManagers as $managerName) {
                    $managerLasts[$managerName] = ($managerLasts[$managerName] ?? 0) + 1;
                }

                // Records
                if ($bestScore > $highestGwScore['points']) {
                    $highestGwScore = ['points' => $bestScore, 'manager' => $bestManagers[0] ?? null, 'gw' => $gw];
                }
                if ($worstScore < $lowestGwScore['points']) {
                    $lowestGwScore = ['points' => $worstScore, 'manager' => $worstManagers[0] ?? null, 'gw' => $gw];
                }

                // --- Streak Calculation ---
                $currentGWManagersScores = $scores->keyBy('manager_id');
                $currentStandingsTemp = [];

                foreach ($managers as $manager) {
                    $score = $currentGWManagersScores->get($manager->id);
                    if ($score) {
                        $managerTotalPoints[$manager->player_name] += $score->points;
                    }
                    $currentStandingsTemp[] = [
                        'name' => $manager->player_name,
                        'total_points' => $managerTotalPoints[$manager->player_name],
                    ];
                }

                // Sort current iteration standings
                usort($currentStandingsTemp, fn($a, $b) => $b['total_points'] <=> $a['total_points']);

                $leaderPoints = $currentStandingsTemp[0]['total_points'];
                $leaders = array_filter($currentStandingsTemp, fn($m) => $m['total_points'] == $leaderPoints);
                $leaderNames = array_column($leaders, 'name');

                // Streak Logic
                if (count($leaderNames) === 1 && $leaderNames[0] === $currentTopManager) {
                    $currentTopStreak++;
                } else {
                    if ($currentTopStreak > $longestTopStreak) {
                        $longestTopStreak = $currentTopStreak;
                        $longestStreakManager = $currentTopManager;
                        $longestStreakStartGW = $streakStartGW;
                    }

                    if (count($leaderNames) === 1) {
                        $currentTopManager = $leaderNames[0];
                        $currentTopStreak = 1;
                        $streakStartGW = $gw;
                    } else {
                        $currentTopManager = null;
                        $currentTopStreak = 0;
                        $streakStartGW = null;
                    }
                }
            } // End Loop

            // Final Streak Check
            if ($currentTopStreak > $longestTopStreak) {
                $longestTopStreak = $currentTopStreak;
                $longestStreakManager = $currentTopManager;
                $longestStreakStartGW = $streakStartGW;
            }

            // 5. Finalize Stats
            $maxLeadCount = collect($managerLeads)->max();
            $mostGWLeads = collect($managerLeads)->filter(fn($c) => $c == $maxLeadCount && $maxLeadCount > 0)->sortDesc()->toArray();

            $maxLastCount = collect($managerLasts)->max();
            $mostGWLasts = collect($managerLasts)->filter(fn($c) => $c == $maxLastCount && $maxLastCount > 0)->sortDesc()->toArray();

            $allManagerNames = $managers->pluck('player_name')->all();
            $bestOrWorstNames = array_keys($managerLeads + $managerLasts);

            // Construct Result Array
            return [
                'isEmpty' => false,
                'standings' => $standings,
                'gwPerformance' => collect($gwPerformance),
                'stats' => [
                    'most_gw_leads' => $mostGWLeads,
                    'most_gw_last' => $mostGWLasts,
                    'highest_gw_score' => $highestGwScore,
                    'lowest_gw_score' => $lowestGwScore,
                    'longest_top_streak' => [
                        'manager' => $longestStreakManager,
                        'length' => $longestTopStreak,
                        'start_gw' => $longestStreakStartGW,
                        'end_gw' => $longestTopStreak > 0 ? ($longestStreakStartGW + $longestTopStreak - 1) : null,
                    ],
                    'the_blowout' => $theBlowout,
                    'wooden_spoon_contenders' => $relegationManagers->pluck('name')->all(),
                    'league_zones' => $leagueZones,
                    'mediocres' => array_values(array_diff($allManagerNames, $bestOrWorstNames)),
                    'men_standing' => array_values(array_diff($allManagerNames, array_keys($managerLasts))),
                    'hall_of_shame' => collect($managerLasts)->filter(fn($c) => $c >= 3)->sortDesc()->toArray(),
                    'hundred_plus_league' => $allScores
                        ->where('points', '>=', 100)
                        ->map(fn($score) => $score->manager->player_name . ' (' . $score->points . ' pts in GW ' . $score->gameweek . ')')
                        ->unique()
                        ->values()
                        ->all(),
                ]
            ];
        });
    }

    /**
     * Flush the stats cache for a specific league.
     * Call this in your update method.
     */
    public function flushLeagueStats(League $league)
    {
        Cache::forget($this->getCacheKey($league->id));
    }

    private function getCacheKey($leagueId)
    {
        return "league_stats_{$leagueId}";
    }

    private function getEmptyStats()
    {
        return [
            'most_gw_leads' => [],
            'most_gw_last' => [],
            'highest_gw_score' => ['manager' => 'N/A', 'points' => 0, 'gw' => null],
            'lowest_gw_score' => ['manager' => 'N/A', 'points' => 0, 'gw' => null],
            'mediocres' => [],
            'men_standing' => [],
            'hall_of_shame' => [],
            'hundred_plus_league' => [],
        ];
    }
}