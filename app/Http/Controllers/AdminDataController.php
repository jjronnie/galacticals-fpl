<?php

namespace App\Http\Controllers;

use App\Jobs\ComputeLeagueGameweekStandingsJob;
use App\Jobs\FetchFplDataJob;
use App\Jobs\FetchLeagueStandings;
use App\Jobs\FetchManagerProfilesJob;
use App\Models\FplPlayer;
use App\Models\FplTeam;
use App\Models\League;
use App\Models\Manager;
use App\Models\ManagerChip;
use App\Services\SyncJobProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDataController extends Controller
{
    public function index(): View
    {
        return view('admin.data.index', [
            'initialPayload' => $this->buildStatusPayload(),
        ]);
    }

    public function leagues(): View
    {
        return view('admin.data.leagues', [
            'initialPayload' => $this->buildStatusPayload(),
        ]);
    }

    public function status(): JsonResponse
    {
        return response()->json($this->buildStatusPayload());
    }

    public function observer(): View
    {
        $teams = FplTeam::query()
            ->withCount('players')
            ->orderBy('name')
            ->paginate(25, ['*'], 'teams_page')
            ->withQueryString();

        $players = FplPlayer::query()
            ->with('team:id,name,short_name')
            ->orderByDesc('total_points')
            ->orderBy('web_name')
            ->paginate(50, ['*'], 'players_page')
            ->withQueryString();

        $chipRecordsCount = ManagerChip::query()->count();

        $chipNames = ManagerChip::query()
            ->select('chip_name')
            ->distinct()
            ->orderBy('chip_name')
            ->pluck('chip_name')
            ->map(fn (string $chipName): string => $this->formatChipName($chipName))
            ->unique()
            ->values();

        return view('admin.data.observer', [
            'teams' => $teams,
            'players' => $players,
            'chipRecordsCount' => $chipRecordsCount,
            'chipNames' => $chipNames,
        ]);
    }

    public function fetchFpl(Request $request): RedirectResponse|JsonResponse
    {
        SyncJobProgressService::queue(
            SyncJobProgressService::FETCH_FPL_DATA,
            3,
            'FPL teams and players sync queued.'
        );

        FetchFplDataJob::dispatch();

        return $this->actionResponse($request, 'FPL teams and players sync queued.');
    }

    public function fetchManagers(Request $request): RedirectResponse|JsonResponse
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

        $entryCount = Manager::query()
            ->whereIn('id', $managerIds)
            ->distinct('entry_id')
            ->count('entry_id');

        if ($entryCount === 0) {
            SyncJobProgressService::complete(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                'No claimed profiles found to sync.'
            );

            return $this->actionResponse($request, 'No claimed profiles found to sync.');
        }

        SyncJobProgressService::queue(
            SyncJobProgressService::FETCH_MANAGER_PROFILES,
            $entryCount,
            "Manager profile sync queued for {$entryCount} claimed entries."
        );

        FetchManagerProfilesJob::dispatch($managerIds);

        return $this->actionResponse($request, "Manager profile sync queued for {$entryCount} claimed entries.");
    }

    public function computeGameweeks(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'league_id' => ['nullable', 'integer', 'exists:leagues,id'],
        ]);

        $leagueId = $request->integer('league_id');

        if ($leagueId > 0) {
            $league = League::find($leagueId);

            if ($league !== null) {
                SyncJobProgressService::queue(
                    SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
                    1,
                    "Gameweek table computation queued for {$league->name}.",
                    false
                );

                ComputeLeagueGameweekStandingsJob::dispatch($league->id, (int) ($league->season ?? now()->year));
            }

            return $this->actionResponse($request, 'League gameweek computation queued.');
        }

        $leagues = League::query()->get(['id', 'season']);

        if ($leagues->isEmpty()) {
            SyncJobProgressService::complete(
                SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
                'No leagues available for gameweek table computation.'
            );

            return $this->actionResponse($request, 'No leagues available for gameweek table computation.');
        }

        SyncJobProgressService::queue(
            SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
            $leagues->count(),
            "Gameweek table computation queued for {$leagues->count()} leagues."
        );

        $leagues->each(function (League $league): void {
            ComputeLeagueGameweekStandingsJob::dispatch($league->id, (int) ($league->season ?? now()->year));
        });

        return $this->actionResponse($request, 'Gameweek computation queued for all leagues.');
    }

    public function refreshLeague(Request $request, League $league): RedirectResponse|JsonResponse
    {
        $league->update([
            'sync_status' => 'processing',
            'sync_message' => 'Manual refresh queued from admin data panel.',
            'synced_managers' => 0,
        ]);

        SyncJobProgressService::queue(
            SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
            1,
            "League refresh queued for {$league->name}.",
            false
        );

        FetchLeagueStandings::dispatch($league->id);

        return $this->actionResponse($request, "League refresh queued for {$league->name}.");
    }

    public function destroyLeague(Request $request, League $league): RedirectResponse|JsonResponse
    {
        $leagueName = (string) $league->name;

        try {
            $league->delete();
        } catch (\Throwable $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to delete league. Please try again.',
                ], 422);
            }

            return back()->withErrors([
                'league' => 'Failed to delete league. Please try again.',
            ]);
        }

        return $this->actionResponse($request, "League {$leagueName} deleted successfully.");
    }

    public function syncAll(Request $request): RedirectResponse|JsonResponse
    {
        $leagues = League::query()->get(['id', 'name', 'season']);

        $managerIds = Manager::query()
            ->whereNotNull('user_id')
            ->pluck('id')
            ->all();

        $profileEntryCount = Manager::query()
            ->whereIn('id', $managerIds)
            ->distinct('entry_id')
            ->count('entry_id');

        SyncJobProgressService::queue(
            SyncJobProgressService::FETCH_FPL_DATA,
            3,
            'Full update queued: syncing FPL teams and players.'
        );
        FetchFplDataJob::dispatch();

        if ($profileEntryCount > 0) {
            SyncJobProgressService::queue(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                $profileEntryCount,
                "Full update queued: syncing {$profileEntryCount} claimed profile entries."
            );
            FetchManagerProfilesJob::dispatch($managerIds);
        } else {
            SyncJobProgressService::complete(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                'No claimed profiles found during full update.'
            );
        }

        if ($leagues->isNotEmpty()) {
            SyncJobProgressService::queue(
                SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
                $leagues->count(),
                "Full update queued: syncing standings for {$leagues->count()} leagues."
            );

            SyncJobProgressService::queue(
                SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
                $leagues->count(),
                "Full update queued: computing gameweek tables for {$leagues->count()} leagues."
            );

            $leagues->each(function (League $league): void {
                $league->update([
                    'sync_status' => 'processing',
                    'sync_message' => 'Full application sync queued from admin panel.',
                    'synced_managers' => 0,
                ]);

                FetchLeagueStandings::dispatch($league->id, false);
                ComputeLeagueGameweekStandingsJob::dispatch($league->id, (int) ($league->season ?? now()->year));
            });
        } else {
            SyncJobProgressService::complete(
                SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
                'No leagues available during full update.'
            );

            SyncJobProgressService::complete(
                SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
                'No leagues available for gameweek table computation.'
            );
        }

        return $this->actionResponse($request, 'Full application data update queued successfully.');
    }

    private function formatChipName(string $chipName): string
    {
        return match (strtolower($chipName)) {
            '3xc' => 'Tripple Captain',
            'bboost' => 'Bench Boost',
            'freehit' => 'Free Hit',
            'wildcard' => 'Wildcard',
            default => strtoupper($chipName),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStatusPayload(): array
    {
        $leagues = League::query()
            ->with('user:id,name,email')
            ->withCount('managers')
            ->orderByDesc('updated_at')
            ->get();

        $claimedManagers = Manager::query()
            ->whereNotNull('user_id')
            ->distinct('entry_id')
            ->count('entry_id');

        $leagueRows = $leagues->map(function (League $league): array {
            $progress = $league->total_managers > 0
                ? (int) round(($league->synced_managers / $league->total_managers) * 100)
                : 0;

            return [
                'id' => $league->id,
                'name' => $league->name,
                'slug' => (string) $league->slug,
                'league_id' => (int) $league->league_id,
                'owner_name' => (string) ($league->user?->name ?? 'Unassigned'),
                'owner_email' => (string) ($league->user?->email ?? '-'),
                'joined_at' => $league->created_at?->toIso8601String(),
                'joined_at_human' => $league->created_at?->diffForHumans(),
                'last_updated_at' => ($league->last_synced_at ?? $league->updated_at)?->toIso8601String(),
                'last_updated_at_human' => ($league->last_synced_at ?? $league->updated_at)?->diffForHumans(),
                'managers_count' => (int) ($league->managers_count ?? 0),
                'sync_status' => (string) ($league->sync_status ?? 'completed'),
                'sync_message' => $league->sync_message ?: '-',
                'synced_managers' => (int) ($league->synced_managers ?? 0),
                'total_managers' => (int) ($league->total_managers ?? 0),
                'progress' => $progress,
            ];
        })->values()->all();

        $jobs = SyncJobProgressService::all();
        $hasRunningJob = collect($jobs)->contains(function (array $job): bool {
            return in_array($job['status'] ?? 'idle', ['queued', 'processing'], true);
        });

        $hasProcessingLeague = collect($leagueRows)->contains(function (array $leagueRow): bool {
            return ($leagueRow['sync_status'] ?? '') === 'processing';
        });

        return [
            'summary' => [
                'total_leagues' => $leagues->count(),
                'claimed_managers' => $claimedManagers,
                'processing_leagues' => $leagues->where('sync_status', 'processing')->count(),
                'failed_leagues' => $leagues->where('sync_status', 'failed')->count(),
            ],
            'jobs' => $jobs,
            'leagues' => $leagueRows,
            'has_running_work' => $hasRunningJob || $hasProcessingLeague,
        ];
    }

    private function actionResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'payload' => $this->buildStatusPayload(),
            ]);
        }

        return back()->with('status', $message);
    }
}
