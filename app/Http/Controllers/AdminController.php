<?php

namespace App\Http\Controllers;

use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SeoService;
use App\Services\SitemapService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\LeagueStatsService;




class AdminController extends Controller
{
    protected $seoService;
    protected $statsService;


    public function __construct(SEOService $seoService, LeagueStatsService $statsService)
    {
        $this->seoService = $seoService;
        $this->statsService = $statsService;
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
            ->with([
                'scores' => function ($query) use ($seasonYear) {
                    $query->where('season_year', $seasonYear)->orderBy('gameweek');
                }
            ])
            ->get();

        // Fetch all gameweek scores for this league and season
        $allScores = $league->gameweekScores()
            ->where('season_year', $seasonYear)
            ->orderBy('gameweek')
            ->get();

        // --- 1. Season Standings (Total Points) ---
        $standings = $managers->map(function ($manager) {
            return [
                'name' => $manager->player_name,
                'team' => $manager->team_name,
                'total_points' => $manager->total_points,
            ];
        })->sortByDesc('total_points')->values();

        $this->seoService->setStandings();


        return view('admin.table', compact('standings', ));
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

        $seasonYear = $league->season;

        $totalManagers = Manager::where('league_id', $league->id)->count();






        $lastUpdated = $league->gameweekScores()
            ->orderBy('gameweek_scores.updated_at', 'desc')
            ->value('gameweek_scores.updated_at');




        return view('dashboard', compact('league', 'totalManagers', 'lastUpdated', ));
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
            return back()->withErrors(['league_taken' => 'This league is already registered by another user, please go to Leagues page to view it']);
        }

        // Fetch League data
        $response = Http::get("https://fantasy.premierleague.com/api/leagues-classic/{$leagueId}/standings/");
        if ($response->failed()) {
            return back()->withErrors(['api_error' => 'Failed to fetch league data. Please Retry after some time']);
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
            // usleep(250000); // 0.25 seconds per manager
        }

        SitemapService::update();

        return redirect()->route('dashboard')->with('status', 'League, managers, and gameweek scores imported successfully, Enjoy!');
    }

    public function updateUserLeague()
    {
        $user = auth()->user();

        // Get user's league (assuming one league per user)
        $league = League::where('user_id', $user->id)->first();

        if (!$league) {
            return back()->withErrors(['no_league' => 'You do not have any league registered yet.']);
        }

        $leagueId = $league->league_id;

        // --- Fetch all pages of standings ---
        $page = 1;
        $allStandings = [];
        $leagueInfo = null;

        do {
            $response = Http::get("https://fantasy.premierleague.com/api/leagues-classic/{$leagueId}/standings/", [
                'page_standings' => $page
            ]);

            if ($response->failed()) {
                return back()->withErrors(['api_error' => 'Failed to fetch league data from FPL API.']);
            }

            $data = $response->json();

            if (!isset($data['league'])) {
                return back()->withErrors(['invalid_league' => 'Invalid league ID or data unavailable.']);
            }

            if (!$leagueInfo) {
                $leagueInfo = $data['league'];
            }

            $standings = $data['standings']['results'] ?? [];
            $allStandings = array_merge($allStandings, $standings);

            $hasNext = $data['standings']['has_next'] ?? false;
            $page++;
            usleep(200000); // prevent rate-limit

        } while ($hasNext);

        // --- Update league info ---
        $league->update([
            'name' => $leagueInfo['name'],
            'admin_name' => $leagueInfo['admin_entry'] ?? null,
            'current_gameweek' => $leagueInfo['start_event'] ?? 0,
            'season' => date('Y'),
        ]);

        // --- Update or insert managers and scores ---
        foreach ($allStandings as $entry) {
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
            if ($historyResponse->failed()) {
                continue;
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

            // usleep(250000); // small delay per manager to stay under limits
        }

        $this->statsService->flushLeagueStats($league);

        SitemapService::update();

        return back()->with('status', 'Your league, managers, and gameweek scores have been updated successfully!');
    }



}