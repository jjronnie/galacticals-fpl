<?php

namespace App\Http\Controllers;

use App\Jobs\ComputeLeagueGameweekStandingsJob;
use App\Jobs\FetchFplDataJob;
use App\Jobs\FetchLeagueStandings;
use App\Jobs\FetchManagerProfilesJob;
use App\Models\League;
use App\Models\Manager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDataController extends Controller
{
    public function index(): View
    {
        $leagues = League::query()
            ->withCount('managers')
            ->orderByDesc('updated_at')
            ->get();

        $claimedManagers = Manager::query()
            ->whereNotNull('user_id')
            ->distinct('entry_id')
            ->count('entry_id');

        return view('admin.data.index', [
            'leagues' => $leagues,
            'claimedManagers' => $claimedManagers,
        ]);
    }

    public function fetchFpl(): RedirectResponse
    {
        FetchFplDataJob::dispatch();

        return back()->with('status', 'FPL teams and players sync queued.');
    }

    public function fetchManagers(Request $request): RedirectResponse
    {
        $request->validate([
            'manager_ids' => ['nullable', 'array'],
            'manager_ids.*' => ['integer', 'exists:managers,id'],
        ]);

        $managerIds = $request->input('manager_ids');

        if ($managerIds === null) {
            $managerIds = Manager::query()
                ->whereNotNull('user_id')
                ->pluck('id')
                ->all();
        }

        FetchManagerProfilesJob::dispatch($managerIds);

        return back()->with('status', 'Manager profile sync queued.');
    }

    public function computeGameweeks(Request $request): RedirectResponse
    {
        $request->validate([
            'league_id' => ['nullable', 'integer', 'exists:leagues,id'],
        ]);

        $leagueId = $request->integer('league_id');

        if ($leagueId > 0) {
            $league = League::find($leagueId);

            if ($league !== null) {
                ComputeLeagueGameweekStandingsJob::dispatch($league->id, (int) ($league->season ?? now()->year));
            }

            return back()->with('status', 'League gameweek computation queued.');
        }

        League::query()->each(function (League $league): void {
            ComputeLeagueGameweekStandingsJob::dispatch($league->id, (int) ($league->season ?? now()->year));
        });

        return back()->with('status', 'Gameweek computation queued for all leagues.');
    }

    public function refreshLeague(League $league): RedirectResponse
    {
        $league->update([
            'sync_status' => 'processing',
            'sync_message' => 'Manual refresh queued from admin data panel.',
            'synced_managers' => 0,
        ]);

        FetchLeagueStandings::dispatch($league->id);

        return back()->with('status', "League refresh queued for {$league->name}.");
    }
}
