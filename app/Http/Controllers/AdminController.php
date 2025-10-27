<?php

namespace App\Http\Controllers;

use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

use Illuminate\Support\Facades\Auth;

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

    public function storeLeague(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        League::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'current_season_year' => date('Y'), // Start with the current year
            'current_gameweek' => 0,
        ]);

        return redirect()->route('dashboard')->with('status', 'League created successfully!');
    }

    // --- Main Dashboard & Stats Generation ---

    public function index()
    {

        // 1. Check for the user's league
        $league = League::where('user_id', Auth::id())->first();

        // 2. If NO league exists, redirect to the setup page
        if (is_null($league)) {
            return redirect()->route('admin.league.create')->with('info', 'Please Complete League Setup!');
        }

        
     
        $seasonYear = $league->current_season_year;

        // Fetch all managers and their scores for the current season
        $managers = Manager::where('league_id', $league->id)
            ->with(['scores' => function($query) use ($seasonYear) {
                $query->where('season_year', $seasonYear)->orderBy('gameweek');
            }])
            ->get();

        $allScores = $league->gameweekScores()
            ->where('season_year', $seasonYear)
            ->orderBy('gameweek')
            ->get();
            
        // --- 1. Season Standings (Total Points) ---
        $standings = $managers->map(function ($manager) use ($seasonYear) {
            $totalPoints = $manager->scores->sum('points');
            return [
                'name' => $manager->name,
                'total_points' => $totalPoints,
            ];
        })->sortByDesc('total_points')->values();
        
        // --- 2. Gameweek-by-Gameweek Breakdown & Aggregates ---
        $gameweeks = $allScores->groupBy('gameweek');
        
        $gwPerformance = [];
        $managerLeads = []; // [Manager Name => Count]
        $managerLasts = []; // [Manager Name => Count]
        $highestGwScore = ['manager' => null, 'points' => 0, 'gw' => null];
        $lowestGwScore = ['manager' => null, 'points' => 9999, 'gw' => null]; // Init high for comparison

        // Process each completed gameweek
        foreach ($gameweeks as $gw => $scores) {
            // Skip if no scores or GW is 0
            if ($scores->isEmpty() || $gw == 0) continue;

            $best = $scores->sortByDesc('points')->first();
            $worst = $scores->sortBy('points')->first();
            
            // Handle ties: collect all managers with best/worst points
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

            // Update Season Highest GW Score
            if ($best->points > $highestGwScore['points']) {
                $highestGwScore['points'] = $best->points;
                $highestGwScore['manager'] = implode(', ', $bestManagers); // Use names, handle ties
                $highestGwScore['gw'] = $gw;
            }
            
            // Update Season Lowest GW Score
            if ($worst->points < $lowestGwScore['points']) {
                $lowestGwScore['points'] = $worst->points;
                $lowestGwScore['manager'] = implode(', ', $worstManagers); // Use names, handle ties
                $lowestGwScore['gw'] = $gw;
            }
        }

        // --- 3. Complex Stats (Mediocres, Men Standing, Hall of Shame, 100+ League) ---
        $allManagerNames = $managers->pluck('name')->all();
        $bestOrWorstNames = array_keys($managerLeads + $managerLasts);
        
        $mediocres = array_values(array_diff($allManagerNames, $bestOrWorstNames));
        
        $menStanding = array_values(array_diff($allManagerNames, array_keys($managerLasts)));
        
        $hallOfShame = array_filter($managerLasts, fn($count) => $count >= 2);
        arsort($hallOfShame); // Sort by times last

        $hundredPlusLeague = $allScores
            ->where('points', '>=', 100)
            ->map(fn($score) => $score->manager->name . ' (' . $score->points . 'pts in GW ' . $score->gameweek . ')')
            ->unique()
            ->values()
            ->all();

        // Prepare view data
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

        return view('dashboard', compact('league', 'managers', 'standings', 'gwPerformance', 'stats'));
    }

    // --- Managers CRUD ---
    
    public function storeManager(Request $request)
    {
        $league = $this->getCurrentLeague();
        $request->validate(['name' => 'required|string|max:255']);
        $request->validate(['team_name' => 'required|string|max:255']);

        Manager::create([
            'league_id' => $league->id,
            'name' => $request->name,
            'team_name' => $request->team_name,
        ]);

        return back()->with('status', 'Manager added!');
    }

    public function destroyManager(Manager $manager)
    {
        // Security check: ensure the manager belongs to the current user's league
        if ($manager->league->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $manager->delete();
        return back()->with('status', 'Manager deleted!');
    }

    // --- Gameweek Score Input ---

    public function createGameweekScore()
    {
        $league = $this->getCurrentLeague();
        $managers = $league->managers;
        $nextGw = $league->current_gameweek + 1;

        if ($managers->isEmpty()) {
             return redirect()->route('dashboard')->with('error', 'Please add managers first!');
        }
        
        // Prevent adding beyond GW 38
        if ($nextGw > 38) {
            return redirect()->route('dashboard')->with('error', 'Season is complete! Start a new season.');
        }

        return view('admin.add-scores', compact('league', 'managers', 'nextGw'));
    }

    public function storeGameweekScore(Request $request)
    {
        $league = $this->getCurrentLeague();
        $nextGw = $league->current_gameweek + 1;
        $seasonYear = $league->current_season_year;
        
        $managers = $league->managers->pluck('id')->toArray();
        $scoresData = $request->input('scores', []);

        // Validation for all managers having a score
        $rules = [];
        foreach ($managers as $managerId) {
            $rules["scores.{$managerId}"] = 'required|integer|min:0';
        }
        $request->validate($rules);
        
        DB::beginTransaction();
        try {
            foreach ($scoresData as $managerId => $points) {
                // Upsert logic (creates or updates, good for corrections)
                GameweekScore::updateOrCreate(
                    [
                        'manager_id' => $managerId,
                        'gameweek' => $nextGw,
                        'season_year' => $seasonYear
                    ],
                    [
                        'points' => $points
                    ]
                );
            }

            // Update league's current gameweek ONLY if it's a new GW being added
            if ($league->current_gameweek < $nextGw) {
                 $league->current_gameweek = $nextGw;
                 $league->save();
            }

            DB::commit();
            return redirect()->route('dashboard')->with('status', "Scores for Gameweek {$nextGw} updated successfully! Stats recalculated.");

        } catch (\Exception $e) {
            DB::rollback();
            // Log the error
            return back()->with('error', 'Error saving scores: ' . $e->getMessage());
        }
    }

    // --- Season Management ---

    public function nextSeason()
    {
        $league = $this->getCurrentLeague();

        if ($league->current_gameweek < 38) {
            return back()->with('error', 'Cannot start a new season until Gameweek 38 is complete.');
        }

        $league->current_season_year = $league->current_season_year + 1; // Increment year
        $league->current_gameweek = 0; // Reset GW
        $league->save();

        return redirect()->route('dashboard')->with('status', 'New season started! Existing season data preserved.');
    }
}