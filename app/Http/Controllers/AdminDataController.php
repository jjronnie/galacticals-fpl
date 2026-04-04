<?php

namespace App\Http\Controllers;

use App\Jobs\ComputeLeagueGameweekStandingsJob;
use App\Jobs\FetchFplDataJob;
use App\Jobs\FetchLeagueStandings;
use App\Jobs\FetchManagerProfilesJob;
use App\Jobs\SyncFixturesJob;
use App\Models\FplFixture;
use App\Models\FplPlayer;
use App\Models\FplSyncRun;
use App\Models\FplTeam;
use App\Models\League;
use App\Models\Manager;
use App\Models\ManagerChip;
use App\Services\FixtureService;
use App\Services\SyncJobProgressService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            'chipRecordsCount' => $chipRecordsCount,
            'chipNames' => $chipNames,
        ]);
    }

    public function teams(): View
    {
        $teams = FplTeam::query()
            ->withCount('players')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $totalPlayers = FplPlayer::query()->count();

        return view('admin.teams.index', compact('teams', 'totalPlayers'));
    }

    public function teamPlayers(int $teamId): View
    {
        $team = FplTeam::query()->withCount('players')->findOrFail($teamId);

        $players = FplPlayer::query()
            ->where('team_id', $teamId)
            ->orderByDesc('total_points')
            ->orderBy('web_name')
            ->paginate(50)
            ->withQueryString();

        return view('admin.teams.players', compact('team', 'players'));
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
        $syncingAllManagers = false;

        if ($managerIds === null) {
            $syncingAllManagers = true;
            $managerIds = Manager::query()
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
                'No manager profiles found to sync.'
            );

            return $this->actionResponse($request, 'No manager profiles found to sync.');
        }

        $scopeLabel = $syncingAllManagers ? 'all' : 'selected';

        SyncJobProgressService::queue(
            SyncJobProgressService::FETCH_MANAGER_PROFILES,
            $entryCount,
            "Manager profile sync queued for {$entryCount} {$scopeLabel} entries."
        );

        Bus::chain([
            new FetchFplDataJob,
            new FetchManagerProfilesJob($managerIds),
        ])->dispatch();

        return $this->actionResponse($request, "Manager profile sync queued for {$entryCount} {$scopeLabel} entries.");
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

        if ($profileEntryCount > 0) {
            SyncJobProgressService::queue(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                $profileEntryCount,
                "Full update queued: syncing {$profileEntryCount} manager profile entries."
            );
        } else {
            SyncJobProgressService::complete(
                SyncJobProgressService::FETCH_MANAGER_PROFILES,
                'No manager profiles found during full update.'
            );
        }

        // Build sequential chain: FPL data → League standings → Manager profiles → Compute tables
        $chain = [new FetchFplDataJob];

        if ($leagues->isNotEmpty()) {
            SyncJobProgressService::queue(
                SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
                $leagues->count(),
                "Full update queued: syncing standings for {$leagues->count()} leagues."
            );

            foreach ($leagues as $league) {
                $league->update([
                    'sync_status' => 'processing',
                    'sync_message' => 'Full application sync queued from admin panel.',
                    'synced_managers' => 0,
                ]);

                $chain[] = new FetchLeagueStandings($league->id, false);
            }
        } else {
            SyncJobProgressService::complete(
                SyncJobProgressService::FETCH_LEAGUE_STANDINGS,
                'No leagues available during full update.'
            );
        }

        if ($profileEntryCount > 0) {
            $chain[] = new FetchManagerProfilesJob($managerIds);
        }

        if ($leagues->isNotEmpty()) {
            SyncJobProgressService::queue(
                SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
                $leagues->count(),
                "Full update queued: computing gameweek tables for {$leagues->count()} leagues."
            );

            foreach ($leagues as $league) {
                $chain[] = new ComputeLeagueGameweekStandingsJob($league->id, (int) ($league->season ?? now()->year));
            }
        } else {
            SyncJobProgressService::complete(
                SyncJobProgressService::COMPUTE_GAMEWEEK_TABLES,
                'No leagues available for gameweek table computation.'
            );
        }

        Bus::chain($chain)->dispatch();

        return $this->actionResponse($request, 'Full application data update queued successfully.');
    }

    public function flushCache(Request $request): RedirectResponse|JsonResponse
    {
        try {
            Cache::flush();
            Artisan::call('optimize:clear');
        } catch (\Throwable $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to clear application cache. Please try again.',
                ], 422);
            }

            return back()->withErrors([
                'cache' => 'Failed to clear application cache. Please try again.',
            ]);
        }

        return $this->actionResponse($request, 'Application cache flushed for all users.');
    }

    public function fixtures(Request $request): View
    {
        $events = FixtureService::getAvailableEvents();
        $currentEvent = $request->integer('event') ?: FixtureService::getCurrentEvent();

        $query = FplFixture::query()->with(['homeTeam', 'awayTeam']);

        if ($currentEvent) {
            $query->where('event', $currentEvent);
        }

        $fixtures = $query->orderBy('kickoff_time')->paginate(50)->withQueryString();

        $totalFixtures = FplFixture::query()->count();
        $finishedFixtures = FplFixture::query()->where('finished', true)->count();
        $upcomingFixtures = FplFixture::query()->where('started', false)->count();
        $liveFixtures = FplFixture::query()->where('started', true)->where('finished', false)->count();

        return view('admin.data.fixtures', compact(
            'fixtures',
            'events',
            'currentEvent',
            'totalFixtures',
            'finishedFixtures',
            'upcomingFixtures',
            'liveFixtures',
        ));
    }

    public function syncFixtures(Request $request): RedirectResponse|JsonResponse
    {
        SyncJobProgressService::queue(
            SyncJobProgressService::SYNC_FIXTURES,
            1,
            'Fixtures sync queued.'
        );

        SyncFixturesJob::dispatch();

        return $this->actionResponse($request, 'Fixtures sync queued.');
    }

    public function jobs(): View
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->get()
            ->map(function (object $row): array {
                $payload = json_decode($row->payload, true);
                $command = $payload['data']['command'] ?? null;
                $jobClass = 'Unknown';

                if ($command !== null) {
                    if (is_string($command)) {
                        $unserialized = @unserialize($command);

                        if ($unserialized !== false) {
                            $jobClass = get_class($unserialized);
                        }
                    } else {
                        $jobClass = get_class($command);
                    }
                }

                $displayName = class_basename($jobClass);
                $exceptionPreview = mb_substr($row->exception, 0, 300);

                return [
                    'id' => $row->id,
                    'uuid' => $row->uuid,
                    'queue' => $row->queue,
                    'job' => $displayName,
                    'job_class' => $jobClass,
                    'exception' => $exceptionPreview,
                    'failed_at' => $row->failed_at,
                    'failed_at_human' => Carbon::parse($row->failed_at)->diffForHumans(),
                ];
            });

        $totalFailed = $failedJobs->count();
        $byJobClass = $failedJobs->groupBy('job')->map->count();

        return view('admin.data.jobs', [
            'failedJobs' => $failedJobs,
            'totalFailed' => $totalFailed,
            'byJobClass' => $byJobClass,
        ]);
    }

    public function retryJob(int $id, QueueManager $queue): RedirectResponse|JsonResponse
    {
        $row = DB::table('failed_jobs')->where('id', $id)->first();

        if ($row === null) {
            return $this->jobActionResponse('Job not found. It may have already been deleted.', 404);
        }

        $payload = json_decode($row->payload, true);

        try {
            $queue->connection($row->connection)->pushRaw(
                $row->payload,
                $row->queue,
                ['delay' => 0]
            );

            DB::table('failed_jobs')->where('id', $id)->delete();
        } catch (\Throwable $exception) {
            return $this->jobActionResponse('Failed to retry job: '.$exception->getMessage(), 422);
        }

        return $this->jobActionResponse('Job requeued successfully.');
    }

    public function deleteJob(int $id): RedirectResponse|JsonResponse
    {
        $deleted = DB::table('failed_jobs')->where('id', $id)->delete();

        if ($deleted === 0) {
            return $this->jobActionResponse('Job not found. It may have already been deleted.', 404);
        }

        return $this->jobActionResponse('Failed job deleted.');
    }

    public function retryAllJobs(QueueManager $queue): RedirectResponse|JsonResponse
    {
        $rows = DB::table('failed_jobs')->get();

        if ($rows->isEmpty()) {
            return $this->jobActionResponse('No failed jobs to retry.');
        }

        $retried = 0;
        $failed = 0;

        foreach ($rows as $row) {
            try {
                $queue->connection($row->connection)->pushRaw(
                    $row->payload,
                    $row->queue,
                    ['delay' => 0]
                );

                DB::table('failed_jobs')->where('id', $row->id)->delete();
                $retried++;
            } catch (\Throwable) {
                $failed++;
            }
        }

        $message = "Requeued {$retried} jobs.";

        if ($failed > 0) {
            $message .= " {$failed} failed to requeue.";
        }

        return $this->jobActionResponse($message);
    }

    public function flushJobs(): RedirectResponse|JsonResponse
    {
        $count = DB::table('failed_jobs')->count();
        DB::table('failed_jobs')->delete();

        return $this->jobActionResponse("Deleted {$count} failed jobs.");
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

        $syncRuns = FplSyncRun::query()
            ->orderByDesc('event')
            ->limit(20)
            ->get()
            ->map(function (FplSyncRun $run): array {
                $meta = $run->meta ?? [];

                return [
                    'id' => $run->id,
                    'event' => $run->event,
                    'status' => $run->status,
                    'synced_at' => $run->synced_at?->toIso8601String(),
                    'synced_at_human' => $run->synced_at?->diffForHumans(),
                    'triggered_by' => $meta['triggered_by'] ?? 'scheduled',
                    'duration_seconds' => $meta['duration_seconds'] ?? null,
                    'gameweek' => $meta['gameweek'] ?? $run->event,
                    'fpl_synced' => (bool) ($meta['fpl_synced'] ?? false),
                    'profile_synced' => (bool) ($meta['profile_synced'] ?? false),
                    'leagues_total' => (int) ($meta['leagues_total'] ?? 0),
                    'leagues_synced' => (int) ($meta['leagues_synced'] ?? 0),
                    'errors_count' => count($meta['errors'] ?? []),
                    'league_failures_count' => count($meta['league_failures'] ?? []),
                ];
            })->values()->all();

        return [
            'summary' => [
                'total_leagues' => $leagues->count(),
                'claimed_managers' => $claimedManagers,
                'processing_leagues' => $leagues->where('sync_status', 'processing')->count(),
                'failed_leagues' => $leagues->where('sync_status', 'failed')->count(),
            ],
            'jobs' => $jobs,
            'leagues' => $leagueRows,
            'sync_runs' => $syncRuns,
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

    private function jobActionResponse(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
