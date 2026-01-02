<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\League;
use App\Models\Manager;
use Illuminate\Support\Facades\Crypt;

use App\Jobs\FetchLeagueStandings;
use App\Jobs\UpdateLeagueStandings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use App\Services\LeagueStatsService;
use App\Services\SeoService;
use App\Services\SitemapService;
class LeagueController extends Controller
{

    protected $seoService;

    protected $statsService;

    public function __construct(LeagueStatsService $statsService, SEOService $seoService) 
    {
        $this->statsService = $statsService;
        $this->seoService = $seoService;
    }
    /**
     * Display a listing of the resource.
     */
    


        public function index()
    {
        // 1. Get the logged-in user's league
        $league = League::where('user_id', Auth::id())->first();

        // 2. Redirect if no league exists
        if (is_null($league)) {
            return redirect()->route('league.create')
                ->with('info', 'Please complete your league setup first.');
        }

        $seasonYear = $league->season;

        $totalManagers = Manager::where('league_id', $league->id)->count();



        $lastUpdated = $league->gameweekScores()
            ->orderBy('gameweek_scores.updated_at', 'desc')
            ->value('gameweek_scores.updated_at');




        return view('leagues.dashboard', compact('league', 'totalManagers', 'lastUpdated', ));
    }


    /**
     * Show the form for creating a new resource.
     */
      public function create()
    {
        if (auth()->user()->league) {
            return redirect()->route('dashboard')
             ->with('info', 'Your League was setup already.');
        }
        return view('leagues.create');
    }


    public function confirm()
{
    $preview = session('league_preview');

    if (!$preview) {
        return redirect()->route('dashboard');
    }

    return view('leagues.confirm', compact('preview'));
}


public function confirmAction(Request $request)
{
    $request->validate([
        'action' => 'required|in:yes,no'
    ]);

     $payload = Crypt::decrypt($request->token);

    if (
        $payload['user_id'] !== auth()->id() ||
        now()->greaterThan($payload['expires_at'])
    ) {
        abort(403);
    }

    $preview = session('league_preview');

    if (!$preview) {
        return redirect()->route('dashboard');
    }

    if ($request->action === 'no') {
        session()->forget('league_preview');
        return redirect()->route('dashboard')
            ->with('status', 'League creation cancelled.');
    }

    // YES: create league
    $league = League::create([
        'user_id' => auth()->id(),
        'league_id' => $preview['league_id'],
        'name' => $preview['name'],
        'admin_name' => $preview['admin_entry'],
        'current_gameweek' => 0,
        'season' => date('Y'),
        'sync_status' => 'processing',
        'sync_message' => 'Fetching league data...',
        'total_managers' => 0,
        'synced_managers' => 0,
    ]);

    session()->forget('league_preview');

    SitemapService::update();

    FetchLeagueStandings::dispatch($league->id);

    return redirect()->route('dashboard')
        ->with('status', 'League confirmed. Import started.');
}



    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
{
    $request->validate([
        'league_id' => 'required|numeric'
    ]);

    $leagueId = $request->league_id;

    // Check existing league
    $existingLeague = League::where('league_id', $leagueId)->first();
    if ($existingLeague) {
        if ($existingLeague->user_id === auth()->id()) {
            return back()->withErrors(['league_exists' => 'You already added this league.']);
        }

        return back()->withErrors([
            'league_taken' => 'This league is already registered by another user.'
        ]);
    }

    try {
        $response = Http::timeout(10)->get(
            "https://fantasy.premierleague.com/api/leagues-classic/{$leagueId}/standings/",
            ['page_standings' => 1]
        );

        if ($response->failed()) {
            return back()->withErrors(['api_error' => 'Unable to fetch league details.']);
        }

        $data = $response->json();

        if (!isset($data['league'])) {
            return back()->withErrors(['invalid_league' => 'Unable to fetch league details. Please verify the league ID.']);
        }

        $leagueInfo = $data['league'];

        // Store temp data in session, NOT database
       $payload = [
        'league_id' => $leagueInfo['id'],
        'user_id' => auth()->id(),
        'expires_at' => now()->addMinutes(10),
    ];

    $token = Crypt::encrypt($payload);

    session([
        'league_preview' => [
            'league_id' => $leagueInfo['id'],
            'name' => $leagueInfo['name'],
              'admin_entry' => $leagueInfo['admin_entry'] ?? null,
        ],
        'league_preview_token' => $token,
    ]);

        return redirect()->route('leagues.confirm');

    } catch (\Throwable $e) {
        \Log::error('League preview error', ['error' => $e->getMessage()]);
        return back()->withErrors(['system_error' => 'Something went wrong.']);
    }
}

    /**
     * Display the specified resource.
     */
        public function show(string $slug, int $gameweek = null)
    {
        // 1. Fetch League (Lightweight query)
        $league = League::where('slug', $slug)->firstOrFail();

        // 2. Determine View State (Target GW)
        $currentGW = $league->gameweek_current;
        $targetGW = $gameweek ?: $currentGW;

        if ($targetGW > $currentGW || $targetGW < 1) {
            $targetGW = $currentGW;
        }

        // 3. Get Heavy Data from Service (Cached)
        $data = $this->statsService->getLeagueStats($league);

        // 4. Handle SEO (Assuming you have this service)
        if (property_exists($this, 'seoService')) {
            $this->seoService->setLeague($league);
        }

        return view('leagues.show', [
            'league' => $league,
            'targetGW' => $targetGW,
            'currentGW' => $currentGW,
            'standings' => $data['standings'],
            'gwPerformance' => $data['gwPerformance'],
            'stats' => $data['stats']
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   public function update()
    {
        $user = auth()->user();
        $league = League::where('user_id', $user->id)->first();

        if (!$league) {
            return back()->withErrors(['no_league' => 'You do not have any league registered yet.']);
        }

        // Check if already syncing
        if ($league->sync_status === 'processing') {
            return back()->withErrors(['already_syncing' => 'League update is already in progress. Please wait for it to complete.']);
        }

        try {
            // Quick API check to ensure league is still accessible
            $response = Http::timeout(10)->get("https://fantasy.premierleague.com/api/leagues-classic/{$league->league_id}/standings/", [
                'page_standings' => 1
            ]);

            if ($response->failed()) {
                return back()->withErrors(['api_error' => 'Failed to connect to Fantasy Premier League . Please try again later.']);
            }

            $data = $response->json();

            if (!isset($data['league'])) {
                return back()->withErrors(['invalid_league' => 'League no longer accessible. It may have been deleted or made private.']);
            }

            // Update status
            $league->update([
                'sync_status' => 'processing',
                'sync_message' => 'Updating league data...',
                'synced_managers' => 0,
            ]);

            

              SitemapService::update();

            // Dispatch background job
            FetchLeagueStandings::dispatch($league->id);

            $this->statsService->flushLeagueStats($league);

            return back()->with('status', 'League update started! Data is being refreshed in the background. This may take a few minutes. See progress in Dashboard');

        } catch (\Exception $e) {
            \Log::error('League Update Error: ' . $e->getMessage());
            return back()->withErrors(['system_error' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getLeagueStatus($leagueId)
    {
        $league = League::where('id', $leagueId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$league) {
            return response()->json(['error' => 'League not found'], 404);
        }

        return response()->json([
            'status' => $league->sync_status,
            'message' => $league->sync_message,
            'progress' => $league->total_managers > 0 
                ? round(($league->synced_managers / $league->total_managers) * 100) 
                : 0,
            'synced_managers' => $league->synced_managers,
            'total_managers' => $league->total_managers,
        ]);
    }


    





public function managers()
{

    

   $user = auth()->user();
        $league = League::where('user_id', $user->id)->first();

        if (!$league) {
            return back()->with('error' ,'You do not have any league registered yet.');
        }

    $page = request()->get('page', 1); // current page
    $perPage = 50;

    // Cache key per league and page
    $cacheKey = "league_{$league->id}_managers_page_{$page}";

    $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($league, $perPage) {
        $managers = $league->managers()
            ->with('gameweekScores')
            ->orderBy('total_points', 'desc')
            ->paginate($perPage);

        // Gameweeks should come from all managers to keep columns stable
        $allManagers = $league->managers()->with('gameweekScores')
        ->get();

       

             $gameweeks = $allManagers
            ->flatMap(fn($m) => $m->gameweekScores->pluck('gameweek'))
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        return [
            'managers' => $managers,
            'gameweeks' => $gameweeks
        ];
    });

   $this->seoService->setStandings();

    return view('leagues.table', [
        'league' => $league,
        'managers' => $data['managers'],
        'gameweeks' => $data['gameweeks'],
    ]);
}
}





