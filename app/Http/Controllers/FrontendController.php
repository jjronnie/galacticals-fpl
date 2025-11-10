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

    $standings = $managers->map(function ($manager) use ($allScores) {
        return [
            'name' => $manager->player_name,
            'team' => $manager->team_name, 
            'total_points' =>$manager->total_points,
        ];
    })->sortByDesc('total_points')->values();

    $gwPerformance = [];
    $managerLeads = [];
    $managerLasts = [];
    $highestGwScore = ['manager' => null, 'points' => 0, 'gw' => null];
    $lowestGwScore = ['manager' => null, 'points' => 9999, 'gw' => null];

    foreach ($gameweeks as $gw => $scores) {
        if ($scores->isEmpty() || $gw == 0) continue;

        // Get the points of the best and worst score for this gameweek
        $bestScore = $scores->sortByDesc('points')->first()->points;
        $worstScore = $scores->sortBy('points')->first()->points;

        // Get ALL managers (names) who achieved the best score (Handles ties for GW best)
        $bestManagers = $scores->where('points', $bestScore)->pluck('manager.player_name')->unique()->values()->all();
        
        // Get ALL managers (names) who achieved the worst score (Handles ties for GW worst)
        $worstManagers = $scores->where('points', $worstScore)->pluck('manager.player_name')->unique()->values()->all();

        $gwPerformance[] = [
            'gameweek' => $gw,
            'best_managers' => $bestManagers,
            'best_points' => $bestScore,
            'worst_managers' => $worstManagers,
            'worst_points' => $worstScore,
        ];

        // Count GW leads and lasts - loop through all tied managers
        foreach ($bestManagers as $managerName) {
            $managerLeads[$managerName] = ($managerLeads[$managerName] ?? 0) + 1;
        }
        foreach ($worstManagers as $managerName) {
            $managerLasts[$managerName] = ($managerLasts[$managerName] ?? 0) + 1;
        }

        // Track highest / lowest GW scores
        if ($bestScore > $highestGwScore['points']) {
            $highestGwScore = [
                'points' => $bestScore,
                // Only storing the first manager name from the array for the overall record
                'manager' => $bestManagers[0] ?? null, 
                'gw' => $gw,
            ];
        }

        if ($worstScore < $lowestGwScore['points']) {
            $lowestGwScore = [
                'points' => $worstScore,
                // Only storing the first manager name from the array for the overall record
                'manager' => $worstManagers[0] ?? null, 
                'gw' => $gw,
            ];
        }
    }

    // --- Updated Logic to Handle Ties for Overall Most Leads/Lasts ---
    
    // Get the highest count of GW leads achieved by any manager
    $maxLeadCount = collect($managerLeads)->max();
    
    // Filter managers to include ALL who achieved the max lead count
    $mostGWLeads = collect($managerLeads)
        ->filter(fn($count) => $count == $maxLeadCount && $maxLeadCount > 0)
        ->sortDesc()
        ->toArray();

    // Get the highest count of GW lasts achieved by any manager
    $maxLastCount = collect($managerLasts)->max();
    
    // Filter managers to include ALL who achieved the max last count
    $mostGWLasts = collect($managerLasts)
        ->filter(fn($count) => $count == $maxLastCount && $maxLastCount > 0)
        ->sortDesc()
        ->toArray();
    
    // --- End of Updated Logic ---

    $allManagerNames = $managers->pluck('player_name')->all();
    $bestOrWorstNames = array_keys($managerLeads + $managerLasts);

    $stats = [
        // Using the new $mostGWLeads and $mostGWLasts arrays
        'most_gw_leads' => $mostGWLeads, 
        'most_gw_last' => $mostGWLasts, 
        'highest_gw_score' => $highestGwScore,
        'lowest_gw_score' => $lowestGwScore,
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
