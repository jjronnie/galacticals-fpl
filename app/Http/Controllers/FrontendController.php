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

    // Updated field
    $seasonYear = $league->season;

    // Updated field name
    $currentGW = $league->gameweek_current;
    $targetGW = $gameweek ?: $currentGW;

    // Validate gameweek
    if ($targetGW > $currentGW || $targetGW < 1) {
        $targetGW = $currentGW;
    }

    $allScores = $league->gameweekScores()
        ->where('season_year', $seasonYear)
        ->orderBy('gameweek')
        ->get();

    $managers = $league->managers;

    if ($allScores->isEmpty()) {
        // Return empty view if no data
        return view('league-stats', [
            'league' => $league,
            'targetGW' => $targetGW,
            'currentGW' => $currentGW,
            'standings' => collect(),
            'gwPerformance' => collect(),
            'stats' => $this->getEmptyStats(),
        ]);
    }

    // --- Calculate stats ---
    $gameweeks = $allScores->groupBy('gameweek');

    $standings = $managers->map(function ($manager) use ($allScores) {
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

    foreach ($gameweeks as $gw => $scores) {
        if ($scores->isEmpty() || $gw == 0) continue;

        $best = $scores->sortByDesc('points')->first();
        $worst = $scores->sortBy('points')->first();

        // Updated to match model naming
        $bestManagers = $scores->where('points', $best->points)->pluck('manager.player_name')->all();
        $worstManagers = $scores->where('points', $worst->points)->pluck('manager.player_name')->all();

        $gwPerformance[] = [
            'gameweek' => $gw,
            'best_managers' => $bestManagers,
            'best_points' => $best->points,
            'worst_managers' => $worstManagers,
            'worst_points' => $worst->points,
        ];

        // Count Leads & Lasts
        foreach ($bestManagers as $name) {
            $managerLeads[$name] = ($managerLeads[$name] ?? 0) + 1;
        }
        foreach ($worstManagers as $name) {
            $managerLasts[$name] = ($managerLasts[$name] ?? 0) + 1;
        }

        // Highest/Lowest GW Scores
        if ($best->points > $highestGwScore['points']) {
            $highestGwScore = [
                'points' => $best->points,
                'manager' => implode(', ', $bestManagers),
                'gw' => $gw,
            ];
        }

        if ($worst->points < $lowestGwScore['points']) {
            $lowestGwScore = [
                'points' => $worst->points,
                'manager' => implode(', ', $worstManagers),
                'gw' => $gw,
            ];
        }
    }

    // --- Complex Stats ---
    $allManagerNames = $managers->pluck('player_name')->all();
    $bestOrWorstNames = array_keys($managerLeads + $managerLasts);

    $stats = [
        'most_gw_leads' => collect($managerLeads)->sortByDesc(null)->toArray(),
        'most_gw_last' => collect($managerLasts)->sortByDesc(null)->toArray(),
        'highest_gw_score' => $highestGwScore,
        'lowest_gw_score' => $lowestGwScore,
        'mediocres' => array_values(array_diff($allManagerNames, $bestOrWorstNames)),
        'men_standing' => array_values(array_diff($allManagerNames, array_keys($managerLasts))),
        'hall_of_shame' => collect($managerLasts)
            ->filter(fn($count) => $count >= 2)
            ->sortByDesc(null)
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
