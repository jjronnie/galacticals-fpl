<?php

namespace App\Http\Controllers;

use App\Helpers\AdminCacheHelper;
use App\Jobs\FetchLeagueStandings;
use App\Models\League;
use App\Models\User;
use App\Services\DashboardStatsService;
use App\Services\LeagueStatsService;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class LeagueController extends Controller
{
    public function __construct(
        private readonly LeagueStatsService $statsService,
        private readonly SeoService $seoService,
        private readonly DashboardStatsService $dashboardStatsService
    ) {}

    public function index(): RedirectResponse|View
    {
        $user = Auth::user();
        $league = $this->resolveDashboardLeague($user);

        if ($league === null) {
            return view('leagues.dashboard-empty', [
                'hasClaimedProfile' => false,
                'hasLeague' => false,
            ]);
        }

        $isOwnedLeague = (int) $league->user_id === (int) $user->id;

        $totalManagers = $league->managers()->count();

        $lastUpdated = $league->gameweekScores()
            ->orderBy('gameweek_scores.updated_at', 'desc')
            ->value('gameweek_scores.updated_at');

        $dashboardStats = $this->dashboardStatsService->getGlobalDashboardStats($league);

        return view('leagues.dashboard', [
            'league' => $league,
            'totalManagers' => $totalManagers,
            'lastUpdated' => $lastUpdated,
            'hasClaimedProfile' => $user->hasClaimedProfile(),
            'isOwnedLeague' => $isOwnedLeague,
            'bestLeagues' => $dashboardStats['best_leagues'] ?? [],
            'mostValuableTeams' => $dashboardStats['most_valuable_teams'] ?? [],
            'playerOfWeekCards' => $dashboardStats['player_of_week_cards'] ?? [],
            'teamOfWeekRows' => $dashboardStats['team_of_week_rows'] ?? [],
        ]);
    }

    public function create(): RedirectResponse|View
    {
        if (auth()->user()->league !== null) {
            return redirect()
                ->route('dashboard')
                ->with('info', 'Your league is already set up.');
        }

        return view('leagues.create');
    }

    public function confirm(): RedirectResponse|View
    {
        $preview = session('league_preview');

        if ($preview === null) {
            return redirect()->route('dashboard');
        }

        return view('leagues.confirm', compact('preview'));
    }

    public function confirmAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:yes,no'],
            'token' => ['required', 'string'],
        ]);

        $payload = Crypt::decrypt($request->string('token')->toString());

        if (
            (int) $payload['user_id'] !== (int) auth()->id()
            || now()->greaterThan($payload['expires_at'])
        ) {
            abort(403);
        }

        $preview = session('league_preview');

        if ($preview === null) {
            return redirect()->route('dashboard');
        }

        if ($request->string('action')->toString() === 'no') {
            session()->forget('league_preview');

            return redirect()
                ->route('dashboard')
                ->with('status', 'League creation cancelled.');
        }

        $league = League::create([
            'user_id' => auth()->id(),
            'league_id' => $preview['league_id'],
            'name' => $preview['name'],
            'admin_name' => $preview['admin_entry'],
            'current_gameweek' => 0,
            'season' => 2025,
            'sync_status' => 'processing',
            'sync_message' => 'Fetching league data...',
            'total_managers' => 0,
            'synced_managers' => 0,
        ]);

        session()->forget('league_preview');

        FetchLeagueStandings::dispatch($league->id);

        return redirect()
            ->route('dashboard')
            ->with('status', 'League confirmed. Import started.');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'league_id' => ['required', 'numeric'],
        ]);

        $leagueId = (int) $request->input('league_id');

        $existingLeague = League::query()->where('league_id', $leagueId)->first();

        if ($existingLeague !== null) {
            if ((int) $existingLeague->user_id === (int) auth()->id()) {
                return back()->withErrors(['league_exists' => 'You already added this league.']);
            }

            return back()->withErrors(['league_taken' => 'This league is already registered by another user.']);
        }

        try {
            $page = 1;
            $allEntries = [];
            $maxManagers = 1000;

            do {
                $response = Http::timeout(10)->get(
                    $this->fplEndpoint("leagues-classic/{$leagueId}/standings/"),
                    ['page_standings' => $page]
                );

                if ($response->failed()) {
                    return back()->withErrors([
                        'api_error' => 'Unable to fetch league details. Verify the league ID and ensure it is a classic league.',
                    ]);
                }

                $data = $response->json();

                if (! isset($data['standings']['results'])) {
                    return back()->withErrors(['invalid_league' => 'Unable to fetch league details.']);
                }

                $entries = $data['standings']['results'];
                $allEntries = array_merge($allEntries, $entries);

                if (count($allEntries) > $maxManagers) {
                    return back()->withErrors([
                        'league_too_large' => "This league has more than {$maxManagers} managers and is not currently supported.",
                    ]);
                }

                $page++;
            } while (! empty($entries));

            if (count($allEntries) < 10) {
                return back()->withErrors(['small_league' => 'Leagues must have at least 10 managers to be registered.']);
            }

            $leagueInfo = $data['league'] ?? ['id' => $leagueId, 'name' => 'Unknown', 'admin_entry' => null];

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
        } catch (\Throwable $exception) {
            \Log::error('League preview error', ['error' => $exception->getMessage()]);

            return back()->withErrors(['system_error' => 'Something went wrong while checking this league.']);
        }
    }

    public function show(string $slug, ?int $gameweek = null): View|RedirectResponse
    {
        if ($gameweek !== null) {
            return redirect()->route('public.leagues.gameweek.show', [
                'slug' => $slug,
                'gameweek' => $gameweek,
            ]);
        }

        $league = League::query()->where('slug', $slug)->firstOrFail();

        $availableGameweeks = $this->statsService->getAvailableGameweeks($league);
        $currentGW = $availableGameweeks !== [] ? max($availableGameweeks) : (int) ($league->current_gameweek ?? 0);

        $data = $this->statsService->getLeagueStats($league);

        $this->seoService->setLeague($league);

        return view('leagues.show', [
            'league' => $league,
            'currentGW' => $currentGW,
            'availableGameweeks' => $availableGameweeks,
            'gwPerformance' => $data['gwPerformance'],
            'stats' => $data['stats'],
        ]);
    }

    public function showPerformance(string $slug): View
    {
        $league = League::query()->where('slug', $slug)->firstOrFail();

        $availableGameweeks = $this->statsService->getAvailableGameweeks($league);
        $currentGW = $availableGameweeks !== [] ? max($availableGameweeks) : (int) ($league->current_gameweek ?? 0);
        $data = $this->statsService->getLeagueStats($league);

        $this->seoService->setLeague($league);

        return view('leagues.performance', [
            'league' => $league,
            'currentGW' => $currentGW,
            'availableGameweeks' => $availableGameweeks,
            'gwPerformance' => $data['gwPerformance'],
        ]);
    }

    public function showGameweek(string $slug, int $gameweek): View|RedirectResponse
    {
        $league = League::query()->where('slug', $slug)->firstOrFail();
        $availableGameweeks = $this->statsService->getAvailableGameweeks($league);
        $leagueStats = $this->statsService->getLeagueStats($league);
        $teamOfWeek = collect($leagueStats['teamOfWeekRows'] ?? [])
            ->firstWhere('gameweek', $gameweek);
        $previousGameweek = collect($availableGameweeks)
            ->filter(fn (int $available): bool => $available < $gameweek)
            ->max();

        if (! in_array($gameweek, $availableGameweeks, true)) {
            return redirect()->route('public.leagues.show', ['slug' => $league->slug]);
        }

        $gameweekStandings = $this->statsService->getGameweekStandings($league, $gameweek)->values();
        $previousStandingsByManager = [];

        if ($previousGameweek !== null) {
            $previousStandingsByManager = $this->statsService
                ->getGameweekStandings($league, (int) $previousGameweek)
                ->keyBy('manager_id')
                ->map(fn ($standing): array => [
                    'rank' => (int) $standing->rank,
                    'points' => (int) $standing->points,
                ])
                ->all();
        }

        $gameweekInsights = null;
        if ($gameweekStandings->isNotEmpty()) {
            $bestPoints = $gameweekStandings->max('points');
            $worstPoints = $gameweekStandings->min('points');

            $bestManagers = $gameweekStandings
                ->where('points', $bestPoints)
                ->filter(fn ($standing): bool => $standing->manager !== null)
                ->map(fn ($standing): array => [
                    'name' => $standing->manager->player_name,
                    'team_name' => $standing->manager->team_name,
                    'entry_id' => $standing->manager->entry_id,
                ])
                ->unique('entry_id')
                ->values()
                ->all();

            $worstManagers = $gameweekStandings
                ->where('points', $worstPoints)
                ->filter(fn ($standing): bool => $standing->manager !== null)
                ->map(fn ($standing): array => [
                    'name' => $standing->manager->player_name,
                    'team_name' => $standing->manager->team_name,
                    'entry_id' => $standing->manager->entry_id,
                ])
                ->unique('entry_id')
                ->values()
                ->all();

            $gameweekInsights = [
                'best_points' => $bestPoints,
                'best_managers' => $bestManagers,
                'worst_points' => $worstPoints,
                'worst_managers' => $worstManagers,
            ];
        }

        $this->seoService->setLeague($league);

        return view('leagues.gameweek', [
            'league' => $league,
            'targetGW' => $gameweek,
            'previousGameweek' => $previousGameweek,
            'previousStandingsByManager' => $previousStandingsByManager,
            'availableGameweeks' => $availableGameweeks,
            'teamOfWeek' => $teamOfWeek,
            'gameweekStandings' => $gameweekStandings,
            'gameweekInsights' => $gameweekInsights,
            'ownershipTrends' => $this->statsService->getOwnershipAndCaptaincyTrends($league, $gameweek),
        ]);
    }

    public function playerOfWeekHistory(): RedirectResponse|View
    {
        $league = $this->resolveDashboardLeague(Auth::user());

        if ($league === null) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['league' => 'Set up a league or claim a profile first to view player of the week history.']);
        }

        return view('leagues.player-of-week-history', [
            'league' => $league,
            'playerOfWeekHistory' => $this->dashboardStatsService->getPlayerOfWeekHistory(),
        ]);
    }

    public function teamOfWeekHistory(Request $request): RedirectResponse|View
    {
        $league = $this->resolveDashboardLeague(Auth::user());

        if ($league === null) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['league' => 'Set up a league or claim a profile first to view team of the week history.']);
        }

        $teamOfWeekHistory = collect($this->dashboardStatsService->getTeamOfWeekHistory());
        $availableGameweeks = $teamOfWeekHistory
            ->pluck('gameweek')
            ->filter()
            ->map(fn ($gameweek): int => (int) $gameweek)
            ->values();
        $selectedGameweek = (int) $request->integer('gameweek', (int) ($availableGameweeks->first() ?? 0));

        if ($availableGameweeks->isNotEmpty() && ! $availableGameweeks->contains($selectedGameweek)) {
            $selectedGameweek = (int) $availableGameweeks->first();
        }

        $selectedTeamOfWeek = $teamOfWeekHistory->firstWhere('gameweek', $selectedGameweek);

        return view('leagues.team-of-week-history', [
            'league' => $league,
            'availableGameweeks' => $availableGameweeks,
            'selectedGameweek' => $selectedGameweek,
            'selectedTeamOfWeek' => $selectedTeamOfWeek,
        ]);
    }

    public function update(): RedirectResponse
    {
        $user = auth()->user();
        $league = League::query()->where('user_id', $user->id)->first();

        if ($league === null) {
            return back()->withErrors(['no_league' => 'You do not have any league registered yet.']);
        }

        if ($league->sync_status === 'processing') {
            return back()->withErrors(['already_syncing' => 'League update is already in progress.']);
        }

        try {
            $response = Http::timeout(10)->get($this->fplEndpoint("leagues-classic/{$league->league_id}/standings/"), [
                'page_standings' => 1,
            ]);

            if ($response->failed()) {
                return back()->withErrors(['api_error' => 'Failed to connect to Fantasy Premier League.']);
            }

            $data = $response->json();

            if (! isset($data['league'])) {
                return back()->withErrors(['invalid_league' => 'League no longer accessible.']);
            }

            $league->update([
                'sync_status' => 'processing',
                'sync_message' => 'Updating league data...',
                'synced_managers' => 0,
            ]);

            FetchLeagueStandings::dispatch($league->id);
            $this->statsService->flushLeagueStats($league);

            return back()->with('status', 'League update started! This may take a few minutes.');
        } catch (\Throwable $exception) {
            \Log::error('League Update Error: '.$exception->getMessage());

            return back()->withErrors(['system_error' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    public function getLeagueStatus(int $leagueId)
    {
        $league = League::query()
            ->where('id', $leagueId)
            ->where('user_id', auth()->id())
            ->first();

        if ($league === null) {
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

    public function managers(): RedirectResponse|View
    {
        $league = $this->resolveDashboardLeague(auth()->user());

        if ($league === null) {
            return back()->with('error', 'You do not have any league or claimed profile yet.');
        }

        $page = (int) request()->get('page', 1);
        $perPage = 50;

        $cacheKey = "league_{$league->id}_managers_page_{$page}";

        $data = AdminCacheHelper::remember($cacheKey, now()->addMinutes(10), function () use ($league, $perPage): array {
            $managers = $league->managers()
                ->with('gameweekScores')
                ->orderBy('total_points', 'desc')
                ->paginate($perPage);

            $allManagers = $league->managers()->with('gameweekScores')->get();

            $gameweeks = $allManagers
                ->flatMap(fn ($manager) => $manager->gameweekScores->pluck('gameweek'))
                ->unique()
                ->sortDesc()
                ->values()
                ->toArray();

            return [
                'managers' => $managers,
                'gameweeks' => $gameweeks,
            ];
        });

        $this->seoService->setStandings();

        return view('leagues.table', [
            'league' => $league,
            'managers' => $data['managers'],
            'gameweeks' => $data['gameweeks'],
        ]);
    }

    private function fplEndpoint(string $path): string
    {
        $baseUrl = rtrim((string) config('services.fpl.base_url', 'https://fantasy.premierleague.com/api'), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }

    private function resolveDashboardLeague(User $user): ?League
    {
        $ownedLeague = League::query()
            ->where('user_id', $user->id)
            ->first();

        if ($ownedLeague !== null) {
            return $ownedLeague;
        }

        return League::query()
            ->whereHas('managers', function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc('last_synced_at')
            ->orderByDesc('updated_at')
            ->first();
    }
}
