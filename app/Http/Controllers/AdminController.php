<?php

namespace App\Http\Controllers;

use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;




class AdminController extends Controller
{
    // Utility method to get the current user's league and season
    private function getCurrentLeague()
    {
        return League::where('user_id', auth()->id())->firstOrFail();
    }

    // --- League Setup ---

    public function createLeague()
    {
        if (auth()->user()->league) {
            return redirect()->route('dashboard');
        }
        // Return a simple form view (you'd need to create this Blade file)
        return view('admin.league-setup'); 
    }



    



    // --- Main Dashboard & Stats Generation ---

public function table()
{
    // 1. Get the logged-in user's league
    $league = League::where('user_id', Auth::id())->first();

    // 2. Redirect if no league exists
    if (is_null($league)) {
        return redirect()->route('admin.league.create')
            ->with('info', 'Please complete your league setup first.');
    }

    // Use the correct DB field
    $seasonYear = $league->season; // Updated from current_season_year

    // Fetch all managers for this league and their scores for the current season
    $managers = Manager::where('league_id', $league->id)
        ->with(['scores' => function ($query) use ($seasonYear) {
            $query->where('season_year', $seasonYear)->orderBy('gameweek');
        }])
        ->get();

    // Fetch all gameweek scores for this league and season
    $allScores = $league->gameweekScores()
        ->where('season_year', $seasonYear)
        ->orderBy('gameweek')
        ->get();

    // --- 1. Season Standings (Total Points) ---
    $standings = $managers->map(function ($manager) {
        $totalPoints = $manager->scores->sum('points');
        return [
            'name' => $manager->player_name, 
            'team' => $manager->team_name, 
            'total_points' => $totalPoints,
        ];
    })->sortByDesc('total_points')->values();

    // --- 2. Gameweek-by-Gameweek Breakdown ---
    $gameweeks = $allScores->groupBy('gameweek');

    $gwPerformance = [];
    $managerLeads = [];
    $managerLasts = [];
    $highestGwScore = ['manager' => null, 'points' => 0, 'gw' => null];
    $lowestGwScore = ['manager' => null, 'points' => 9999, 'gw' => null];

    foreach ($gameweeks as $gw => $scores) {
        if ($scores->isEmpty() || $gw == 0) continue;

        $best = $scores->sortByDesc('points')->first();
        $worst = $scores->sortBy('points')->first();

        $bestManagers = $scores->where('points', $best->points)->pluck('manager.player_name')->all();
        $worstManagers = $scores->where('points', $worst->points)->pluck('manager.player_name')->all();

        $gwPerformance[] = [
            'gameweek' => $gw,
            'best_managers' => $bestManagers,
            'best_points' => $best->points,
            'worst_managers' => $worstManagers,
            'worst_points' => $worst->points,
        ];

        foreach ($bestManagers as $name) {
            $managerLeads[$name] = ($managerLeads[$name] ?? 0) + 1;
        }
        foreach ($worstManagers as $name) {
            $managerLasts[$name] = ($managerLasts[$name] ?? 0) + 1;
        }

        if ($best->points > $highestGwScore['points']) {
            $highestGwScore = [
                'manager' => implode(', ', $bestManagers),
                'points' => $best->points,
                'gw' => $gw,
            ];
        }

        if ($worst->points < $lowestGwScore['points']) {
            $lowestGwScore = [
                'manager' => implode(', ', $worstManagers),
                'points' => $worst->points,
                'gw' => $gw,
            ];
        }
    }

    // --- 3. Stats ---
    $allManagerNames = $managers->pluck('player_name')->all();
    $bestOrWorstNames = array_keys($managerLeads + $managerLasts);

    $mediocres = array_values(array_diff($allManagerNames, $bestOrWorstNames));
    $menStanding = array_values(array_diff($allManagerNames, array_keys($managerLasts)));
    $hallOfShame = array_filter($managerLasts, fn($count) => $count >= 2);
    arsort($hallOfShame);

    $hundredPlusLeague = $allScores
        ->where('points', '>=', 100)
        ->map(fn($score) => $score->manager->player_name . ' (' . $score->points . ' pts in GW ' . $score->gameweek . ')')
        ->unique()
        ->values()
        ->all();

    $stats = [
        'most_gw_leads' => $managerLeads,
        'most_gw_last' => $managerLasts,
        'highest_gw_score' => $highestGwScore,
        'lowest_gw_score' => $lowestGwScore,
        'mediocres' => $mediocres,
        'men_standing' => $menStanding,
        'hall_of_shame' => $hallOfShame,
        'hundred_plus_league' => $hundredPlusLeague,
    ];

    return view('admin.table', compact('league', 'managers', 'standings', ));
}






    // --- Main Dashboard & Stats Generation ---

public function index()
{
    // 1. Get the logged-in user's league
    $league = League::where('user_id', Auth::id())->first();

    // 2. Redirect if no league exists
    if (is_null($league)) {
        return redirect()->route('admin.league.create')
            ->with('info', 'Please complete your league setup first.');
    }

    // Use the correct DB field
    $seasonYear = $league->season; // Updated from current_season_year

    // Fetch all managers for this league and their scores for the current season
    $managers = Manager::where('league_id', $league->id)
        ->with(['scores' => function ($query) use ($seasonYear) {
            $query->where('season_year', $seasonYear)->orderBy('gameweek');
        }])
        ->get();

    // Fetch all gameweek scores for this league and season
    $allScores = $league->gameweekScores()
        ->where('season_year', $seasonYear)
        ->orderBy('gameweek')
        ->get();

    // --- 1. Season Standings (Total Points) ---
    $standings = $managers->map(function ($manager) {
        $totalPoints = $manager->scores->sum('points');
        return [
            'name' => $manager->player_name, // Player name stays as frontend expects
            'total_points' => $totalPoints,
        ];
    })->sortByDesc('total_points')->values();

    // --- 2. Gameweek-by-Gameweek Breakdown ---
    $gameweeks = $allScores->groupBy('gameweek');

    $gwPerformance = [];
    $managerLeads = [];
    $managerLasts = [];
    $highestGwScore = ['manager' => null, 'points' => 0, 'gw' => null];
    $lowestGwScore = ['manager' => null, 'points' => 9999, 'gw' => null];

    foreach ($gameweeks as $gw => $scores) {
        if ($scores->isEmpty() || $gw == 0) continue;

        $best = $scores->sortByDesc('points')->first();
        $worst = $scores->sortBy('points')->first();

        $bestManagers = $scores->where('points', $best->points)->pluck('manager.player_name')->all();
        $worstManagers = $scores->where('points', $worst->points)->pluck('manager.player_name')->all();

        $gwPerformance[] = [
            'gameweek' => $gw,
            'best_managers' => $bestManagers,
            'best_points' => $best->points,
            'worst_managers' => $worstManagers,
            'worst_points' => $worst->points,
        ];

        foreach ($bestManagers as $name) {
            $managerLeads[$name] = ($managerLeads[$name] ?? 0) + 1;
        }
        foreach ($worstManagers as $name) {
            $managerLasts[$name] = ($managerLasts[$name] ?? 0) + 1;
        }

        if ($best->points > $highestGwScore['points']) {
            $highestGwScore = [
                'manager' => implode(', ', $bestManagers),
                'points' => $best->points,
                'gw' => $gw,
            ];
        }

        if ($worst->points < $lowestGwScore['points']) {
            $lowestGwScore = [
                'manager' => implode(', ', $worstManagers),
                'points' => $worst->points,
                'gw' => $gw,
            ];
        }
    }

    // --- 3. Stats ---
    $allManagerNames = $managers->pluck('player_name')->all();
    $bestOrWorstNames = array_keys($managerLeads + $managerLasts);

    $mediocres = array_values(array_diff($allManagerNames, $bestOrWorstNames));
    $menStanding = array_values(array_diff($allManagerNames, array_keys($managerLasts)));
    $hallOfShame = array_filter($managerLasts, fn($count) => $count >= 3);
    arsort($hallOfShame);

    $hundredPlusLeague = $allScores
        ->where('points', '>=', 100)
        ->map(fn($score) => $score->manager->player_name . ' (' . $score->points . ' pts in GW ' . $score->gameweek . ')')
        ->unique()
        ->values()
        ->all();

    $stats = [
        'most_gw_leads' => $managerLeads,
        'most_gw_last' => $managerLasts,
        'highest_gw_score' => $highestGwScore,
        'lowest_gw_score' => $lowestGwScore,
        'mediocres' => $mediocres,
        'men_standing' => $menStanding,
        'hall_of_shame' => $hallOfShame,
        'hundred_plus_league' => $hundredPlusLeague,
    ];

    return view('dashboard', compact('league', 'managers',  'gwPerformance', 'stats'));
}

     

    


public function storeLeague(Request $request)
{
    $request->validate([
        'league_id' => 'required|numeric'
    ]);

    $leagueId = $request->league_id;

    // Check if league already exists and belongs to another user
    $existingLeague = League::where('league_id', $leagueId)->first();
    if ($existingLeague) {
        if ($existingLeague->user_id === auth()->id()) {
            return back()->withErrors(['league_exists' => 'You already added this league.']);
        }
        return back()->withErrors(['league_taken' => 'This league is already registered by another user.']);
    }

    // Fetch League data
    $response = Http::get("https://fantasy.premierleague.com/api/leagues-classic/{$leagueId}/standings/");
    if ($response->failed()) {
        return back()->withErrors(['api_error' => 'Failed to fetch league data from FPL API.']);
    }

    $data = $response->json();

    if (!isset($data['league'])) {
        return back()->withErrors(['invalid_league' => 'Invalid league ID or data unavailable.']);
    }

    // Extract league info
    $leagueInfo = $data['league'];
    $standings = $data['standings']['results'] ?? [];

    // Store league
    $league = League::create([
        'user_id' => auth()->id(),
        'league_id' => $leagueInfo['id'],
        'name' => $leagueInfo['name'],
        'admin_name' => $leagueInfo['admin_entry'] ?? null,
        'current_gameweek' => $data['standings']['has_next'] ? $data['standings']['page'] : 0,
        'season' => date('Y'),
    ]);

    // Process each manager
    foreach ($standings as $entry) {
        $manager = Manager::updateOrCreate(
            [
                'league_id' => $league->id,
                'entry_id' => $entry['entry'],
            ],
            [
                'player_name' => $entry['player_name'],
                'team_name' => $entry['entry_name'],
                'rank' => $entry['rank'],
                'total_points' => $entry['total'],
            ]
        );

        // Fetch manager gameweek history from FPL API
        $historyResponse = Http::get("https://fantasy.premierleague.com/api/entry/{$entry['entry']}/history/");
        if ($historyResponse->failed()) {
            continue; // skip this manager if their data fails
        }

        $historyData = $historyResponse->json();

        if (!isset($historyData['current'])) {
            continue;
        }

        foreach ($historyData['current'] as $week) {
            GameweekScore::updateOrCreate(
                [
                    'manager_id' => $manager->id,
                    'gameweek' => $week['event'],
                    'season_year' => date('Y'),
                ],
                [
                    'points' => $week['points'],
                ]
            );
        }

        // Optional: sleep to avoid rate-limiting (FPL API is sensitive)
        usleep(250000); // 0.25 seconds per manager
    }

    return redirect()->route('dashboard')->with('status', 'League, managers, and gameweek scores imported successfully!');
}

public function updateUserLeague()
{
    $user = auth()->user();

    // Get user's league(s) - assuming one league per user for simplicity
    $league = League::where('user_id', $user->id)->first();

    if (!$league) {
        return back()->withErrors(['no_league' => 'You do not have any league registered yet.']);
    }

    $leagueId = $league->league_id;

    // Fetch league data from FPL API
    $response = Http::get("https://fantasy.premierleague.com/api/leagues-classic/{$leagueId}/standings/");
    if ($response->failed()) {
        return back()->withErrors(['api_error' => 'Failed to fetch league data from FPL API.']);
    }

    $data = $response->json();

    if (!isset($data['league'])) {
        return back()->withErrors(['invalid_league' => 'Invalid league ID or data unavailable.']);
    }

    $leagueInfo = $data['league'];
    $standings = $data['standings']['results'] ?? [];

    // Update league info
    $league->update([
        'name' => $leagueInfo['name'],
        'admin_name' => $leagueInfo['admin_entry'] ?? null,
        'current_gameweek' => $data['standings']['has_next'] ? $data['standings']['page'] : 0,
        'season' => date('Y'),
    ]);

    // Update managers and gameweek scores
    foreach ($standings as $entry) {
        $manager = Manager::updateOrCreate(
            [
                'league_id' => $league->id,
                'entry_id' => $entry['entry'],
            ],
            [
                'player_name' => $entry['player_name'],
                'team_name' => $entry['entry_name'],
                'rank' => $entry['rank'],
                'total_points' => $entry['total'],
            ]
        );

        // Fetch manager gameweek history
        $historyResponse = Http::get("https://fantasy.premierleague.com/api/entry/{$entry['entry']}/history/");
        if ($historyResponse->failed()) continue;

        $historyData = $historyResponse->json();

        if (!isset($historyData['current'])) continue;

        foreach ($historyData['current'] as $week) {
            GameweekScore::updateOrCreate(
                [
                    'manager_id' => $manager->id,
                    'gameweek' => $week['event'],
                    'season_year' => date('Y'),
                ],
                [
                    'points' => $week['points'],
                ]
            );
        }

        usleep(250000); // avoid rate limits
    }

    return back()->with('status', 'Your league, managers, and gameweek scores have been updated successfully!');
}


}