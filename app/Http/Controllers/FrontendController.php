<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\League;

class FrontendController extends Controller
{
    public function listLeagues()
    {
        $leagues = League::all(['name', 'slug']);

        return view('leagues-list', compact('leagues'));
    }

public function showStats(string $slug, int $gameweek = null)
{
    $league = League::where('slug', $slug)
        ->with('managers.scores')
        ->firstOrFail();

    $seasonYear = $league->season;
    $currentGW = $league->gameweek_current;
    $targetGW = $gameweek ?: $currentGW;

    if ($targetGW > $currentGW || $targetGW < 1) {
        $targetGW = $currentGW;
    }

    $allScores = $league->gameweekScores()
        ->where('season_year', $seasonYear)
        ->orderBy('gameweek')
        ->get();

    $managers = $league->managers;

    if ($allScores->isEmpty()) {
        return view('league-stats', [
            'league' => $league,
            'targetGW' => $targetGW,
            'currentGW' => $currentGW,
            'standings' => collect(),
            'gwPerformance' => collect(),
            'stats' => $this->getEmptyStats(),
        ]);
    }

    // Group scores by gameweek
    $gameweeks = $allScores->groupBy('gameweek');

    // Initial Standings (for League Zones and Wooden Spoon Contenders)
    $standings = $managers->map(function ($manager) use ($allScores) {
        return [
            'name' => $manager->player_name,
            'team' => $manager->team_name, 
            'total_points' => $manager->total_points,
        ];
    })->sortByDesc('total_points')->values();
    
    // League Zone Logic
    $topManagers = $standings->take(5);
    $relegationManagers = $standings->slice(-3)->values();
    $woodenSpoonContenders = $relegationManagers->pluck('name')->all();

    $leagueZones = [
        'champions_league' => $topManagers->take(4)->pluck('name')->all(),
        'europa_league' => $topManagers->slice(4, 1)->pluck('name')->all(),
        'relegation_zone' => $relegationManagers->pluck('name')->all(),
    ];

    // Initialize variables for streaks and new stats
    $managerTotalPoints = $managers->pluck('id', 'player_name')->map(fn() => 0)->toArray();
    $currentTopStreak = 0;
    $currentTopManager = null;
    $longestTopStreak = 0;
    $longestStreakManager = null;
    $longestStreakStartGW = null;
    $streakStartGW = null;
    
    // New Stat Initializations
    $theBlowout = ['difference' => 0, 'gw' => null];
    // Removed: $worstCaptainScore = ['points' => 9999, 'manager' => null, 'gw' => null];
    
    $gwPerformance = [];
    $managerLeads = [];
    $managerLasts = [];
    $highestGwScore = ['manager' => null, 'points' => 0, 'gw' => null];
    $lowestGwScore = ['manager' => null, 'points' => 9999, 'gw' => null];

    foreach ($gameweeks as $gw => $scores) {
        if ($scores->isEmpty() || $gw == 0) continue;

        // 1. Calculate GW Best/Worst and update Leads/Lasts
        $bestScore = $scores->sortByDesc('points')->first()->points;
        $worstScore = $scores->sortBy('points')->first()->points;
        $scoreDifference = $bestScore - $worstScore; 

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

        // Removed: Check for Worst Captain Score (PROXY: Lowest GW Score Ever)
        // if ($worstScore < $worstCaptainScore['points']) { ... }

        $gwPerformance[] = [
            'gameweek' => $gw,
            'best_managers' => $bestManagers,
            'best_points' => $bestScore,
            'worst_managers' => $worstManagers,
            'worst_points' => $worstScore,
        ];

        // Update Leads/Lasts and Highest/Lowest GW Score logic
        foreach ($bestManagers as $managerName) {
            $managerLeads[$managerName] = ($managerLeads[$managerName] ?? 0) + 1;
        }
        foreach ($worstManagers as $managerName) {
            $managerLasts[$managerName] = ($managerLasts[$managerName] ?? 0) + 1;
        }

        if ($bestScore > $highestGwScore['points']) {
            $highestGwScore = [
                'points' => $bestScore,
                'manager' => $bestManagers[0] ?? null, 
                'gw' => $gw,
            ];
        }

        if ($worstScore < $lowestGwScore['points']) {
            $lowestGwScore = [
                'points' => $worstScore,
                'manager' => $worstManagers[0] ?? null, 
                'gw' => $gw,
            ];
        }
        
        // 2. Update Total Points for Streak Calculation
        $currentGWManagersScores = $scores->keyBy('manager_id');
        $currentStandings = [];

        foreach ($managers as $manager) {
            $score = $currentGWManagersScores->get($manager->id);
            if ($score) {
                $managerTotalPoints[$manager->player_name] += $score->points;
            }
            $currentStandings[] = [
                'name' => $manager->player_name,
                'total_points' => $managerTotalPoints[$manager->player_name],
            ];
        }

        // Sort the current standings by total points (desc)
        usort($currentStandings, fn($a, $b) => $b['total_points'] <=> $a['total_points']);

        $leaderPoints = $currentStandings[0]['total_points'];
        $leaders = collect($currentStandings)->filter(fn($m) => $m['total_points'] == $leaderPoints)->pluck('name')->all();

        // 3. Track Longest Top Streak
        if (count($leaders) === 1 && $leaders[0] === $currentTopManager) {
            $currentTopStreak++;
        } else {
            if ($currentTopStreak > $longestTopStreak) {
                $longestTopStreak = $currentTopStreak;
                $longestStreakManager = $currentTopManager;
                $longestStreakStartGW = $streakStartGW;
            }

            if (count($leaders) === 1) {
                $currentTopManager = $leaders[0];
                $currentTopStreak = 1;
                $streakStartGW = $gw;
            } else {
                $currentTopManager = null;
                $currentTopStreak = 0;
                $streakStartGW = null;
            }
        }
    }
    
    // Final check for the last calculated streak
    if ($currentTopStreak > $longestTopStreak) {
        $longestTopStreak = $currentTopStreak;
        $longestStreakManager = $currentTopManager;
        $longestStreakStartGW = $streakStartGW;
    }

    // Handle overall most GW leads/lasts ties
    $maxLeadCount = collect($managerLeads)->max();
    $mostGWLeads = collect($managerLeads)
        ->filter(fn($count) => $count == $maxLeadCount && $maxLeadCount > 0)
        ->sortDesc()
        ->toArray();

    $maxLastCount = collect($managerLasts)->max();
    $mostGWLasts = collect($managerLasts)
        ->filter(fn($count) => $count == $maxLastCount && $maxLastCount > 0)
        ->sortDesc()
        ->toArray();

    $longestStreakStat = [
        'manager' => $longestStreakManager,
        'length' => $longestTopStreak,
        'start_gw' => $longestStreakStartGW,
        'end_gw' => $longestTopStreak > 0 ? ($longestStreakStartGW + $longestTopStreak - 1) : null,
    ];

    $allManagerNames = $managers->pluck('player_name')->all();
    $bestOrWorstNames = array_keys($managerLeads + $managerLasts);

    $stats = [
        'most_gw_leads' => $mostGWLeads, 
        'most_gw_last' => $mostGWLasts, 
        'highest_gw_score' => $highestGwScore,
        'lowest_gw_score' => $lowestGwScore,
        'longest_top_streak' => $longestStreakStat,
        
        // --- New Stats ---
        'the_blowout' => $theBlowout,
        'wooden_spoon_contenders' => $woodenSpoonContenders,
        // Removed: 'captains_curse_proxy' => $worstCaptainScore,
        'league_zones' => $leagueZones,
        // --- End New Stats ---
        
        'mediocres' => array_values(array_diff($allManagerNames, $bestOrWorstNames)),
        'men_standing' => array_values(array_diff($allManagerNames, array_keys($managerLasts))),
        'hall_of_shame' => collect($managerLasts)
            ->filter(fn($count) => $count >= 3)
            ->sortDesc()
            ->toArray(),
        'hundred_plus_league' => $allScores
            ->where('points', '>=', 100)
            ->map(fn($score) => $score->manager->player_name . ' (' . $score->points . ' pts in GW ' . $score->gameweek . ')')
            ->unique()
            ->values()
            ->all(),
    ];

    return view('league-stats', compact('league', 'targetGW', 'currentGW', 'standings', 'gwPerformance', 'stats'));
}


    
/**
 * Return a default empty stats structure.
 * Prevents null/undefined errors when no data is available.
 */
private function getEmptyStats()
{
    return [
        'most_gw_leads' => [],
        'most_gw_last' => [],
        'highest_gw_score' => [
            'manager' => 'N/A',
            'points' => 0,
            'gw' => null,
        ],
        'lowest_gw_score' => [
            'manager' => 'N/A',
            'points' => 0,
            'gw' => null,
        ],
        'mediocres' => [],
        'men_standing' => [],
        'hall_of_shame' => [],
        'hundred_plus_league' => [],
    ];
}




}
