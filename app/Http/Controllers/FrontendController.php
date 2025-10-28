<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\League;

class FrontendController extends Controller
{
    public function listLeagues()
    {
        $leagues = League::all(['name', 'league_id']);

        return view('leagues-list', compact('leagues'));
    }

    public function showStats(string $league_id, int $gameweek = null)
    {
        $league = League::where('league_id', $league_id)
            ->with('managers.scores')
            ->firstOrFail();

        $seasonYear = $league->current_season_year;
        
        // Determine the target GW. If not provided, use the current GW.
        $currentGW = $league->current_gameweek;
        $targetGW = $gameweek ?: $currentGW;
        
        // Ensure the target GW is valid (between 1 and current GW)
        if ($targetGW > $currentGW || $targetGW < 1) {
            $targetGW = $currentGW;
        }

        $allScores = $league->gameweekScores()
            ->where('season_year', $seasonYear)
            ->where('gameweek', '<=', $currentGW) // Only include completed GWs for stats
            ->orderBy('gameweek')
            ->get();
            
        $managers = $league->managers;

        if ($allScores->isEmpty()) {
            // Handle case where league exists but no scores are recorded
             return view('league-stats', [
                'league' => $league,
                'targetGW' => $targetGW,
                'currentGW' => $currentGW,
                'standings' => collect(),
                'gwPerformance' => collect(),
                'stats' => $this->getEmptyStats(), // Return empty stats
            ]);
        }
        
        // --- Stat Calculation (Copied/Adapted from AdminController for self-contained use) ---
        
        $gameweeks = $allScores->groupBy('gameweek');
        
        $standings = $managers->map(function ($manager) use ($seasonYear, $allScores) {
            $totalPoints = $allScores->where('manager_id', $manager->id)->sum('points');
            return [
                'name' => $manager->player_name,
                'total_points' => $totalPoints,
            ];
        })->sortByDesc('total_points')->values();
        
        $gwPerformance = [];
        $managerLeads = []; 
        $managerLasts = []; 
        $highestGwScore = ['manager' => null, 'points' => 0, 'gw' => null];
        $lowestGwScore = ['manager' => null, 'points' => 9999, 'gw' => null];

        // Process each completed gameweek
        foreach ($gameweeks as $gw => $scores) {
            if ($scores->isEmpty() || $gw == 0) continue;

            $best = $scores->sortByDesc('points')->first();
            $worst = $scores->sortBy('points')->first();
            
            $bestManagers = $scores->where('points', $best->points)->pluck('manager.name')->all();
            $worstManagers = $scores->where('points', $worst->points)->pluck('manager.name')->all();

            $gwPerformance[] = [
                'gameweek' => $gw,
                'best_managers' => $bestManagers,
                'best_points' => $best->points,
                'worst_managers' => $worstManagers,
                'worst_points' => $worst->points,
            ];
            
            // Count Leads and Lasts
            foreach ($bestManagers as $name) {
                $managerLeads[$name] = ($managerLeads[$name] ?? 0) + 1;
            }
            foreach ($worstManagers as $name) {
                $managerLasts[$name] = ($managerLasts[$name] ?? 0) + 1;
            }

            // Update Season Highest/Lowest GW Score
            if ($best->points > $highestGwScore['points']) {
                $highestGwScore = ['points' => $best->points, 'manager' => implode(', ', $bestManagers), 'gw' => $gw];
            }
            if ($worst->points < $lowestGwScore['points']) {
                $lowestGwScore = ['points' => $worst->points, 'manager' => implode(', ', $worstManagers), 'gw' => $gw];
            }
        }

        // Complex Stats
        $allManagerNames = $managers->pluck('name')->all();
        $bestOrWorstNames = array_keys($managerLeads + $managerLasts);
        
        $stats = [
            'most_gw_leads' => collect($managerLeads)->sortByDesc(null)->toArray(), // Sort for displaying
            'most_gw_last' => collect($managerLasts)->sortByDesc(null)->toArray(), // Sort for displaying
            'highest_gw_score' => $highestGwScore,
            'lowest_gw_score' => $lowestGwScore,
            'mediocres' => array_values(array_diff($allManagerNames, $bestOrWorstNames)),
            'men_standing' => array_values(array_diff($allManagerNames, array_keys($managerLasts))),
            'hall_of_shame' => collect($managerLasts)->filter(fn($count) => $count >= 2)->sortByDesc(null)->toArray(),
            'hundred_plus_league' => $allScores
                ->where('points', '>=', 100)
                ->map(fn($score) => $score->manager->name . ' (' . $score->points . 'pts in GW ' . $score->gameweek . ')')
                ->unique()
                ->values()
                ->all(),
        ];
        
        // --- END Stat Calculation ---

        return view('league-stats', compact('league', 'targetGW', 'currentGW', 'standings', 'gwPerformance', 'stats'));
    }
    
    private function getEmptyStats()
    {
        return [
            'most_gw_leads' => [], 
            'most_gw_last' => [], 
            'highest_gw_score' => ['manager' => 'N/A', 'points' => 0, 'gw' => 'N/A'],
            'lowest_gw_score' => ['manager' => 'N/A', 'points' => 0, 'gw' => 'N/A'],
            'mediocres' => [],
            'men_standing' => [],
            'hall_of_shame' => [],
            'hundred_plus_league' => [],
        ];
    }
}
