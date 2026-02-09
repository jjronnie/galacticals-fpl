<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminManagerActionRequest;
use App\Models\Manager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminManagerController extends Controller
{
    public function all(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $page = max((int) $request->query('page', 1), 1);

        return view('admin.managers.all', [
            'initialPayload' => $this->paginatedManagerOverview($search, $page),
            'initialSearch' => $search,
            'resultsUrl' => route('admin.managers.all.results'),
        ]);
    }

    public function allResults(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $page = max((int) $request->query('page', 1), 1);

        return response()->json(
            $this->paginatedManagerOverview($search, $page)
        );
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $canonicalIds = Manager::query()
            ->from('managers as m')
            ->whereNotNull('m.user_id')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('m.team_name', 'like', "%{$search}%")
                        ->orWhere('m.player_name', 'like', "%{$search}%")
                        ->orWhereExists(function ($userQuery) use ($search): void {
                            $userQuery
                                ->selectRaw('1')
                                ->from('users')
                                ->whereColumn('users.id', 'm.user_id')
                                ->where(function ($credentials) use ($search): void {
                                    $credentials->where('users.name', 'like', "%{$search}%")
                                        ->orWhere('users.email', 'like', "%{$search}%");
                                });
                        })
                        ->orWhere('m.entry_id', $search);
                });
            })
            ->when($status === 'suspended', fn ($query) => $query->whereNotNull('m.suspended_at'))
            ->when($status === 'active', fn ($query) => $query->whereNull('m.suspended_at'))
            ->groupBy('m.entry_id')
            ->selectRaw('MAX(m.id)');

        $managers = Manager::query()
            ->with(['user:id,name,email'])
            ->whereIn('id', $canonicalIds)
            ->orderByDesc('claimed_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.managers.index', [
            'managers' => $managers,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function suspend(AdminManagerActionRequest $request, Manager $manager): RedirectResponse
    {
        Manager::query()
            ->where('entry_id', $manager->entry_id)
            ->update([
                'suspended_at' => now(),
                'notes' => $request->validated('reason'),
            ]);

        return back()->with('status', 'Manager profile suspended successfully.');
    }

    public function unsuspend(AdminManagerActionRequest $request, Manager $manager): RedirectResponse
    {
        Manager::query()
            ->where('entry_id', $manager->entry_id)
            ->update([
                'suspended_at' => null,
                'notes' => $request->validated('reason'),
            ]);

        return back()->with('status', 'Manager profile unsuspended successfully.');
    }

    public function disband(AdminManagerActionRequest $request, Manager $manager): RedirectResponse
    {
        Manager::query()
            ->where('entry_id', $manager->entry_id)
            ->update([
                'user_id' => null,
                'claimed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'suspended_at' => null,
                'notes' => $request->validated('reason'),
            ]);

        return back()->with('status', 'Manager claim disbanded successfully.');
    }

    private function paginatedManagerOverview(string $search, int $page, int $perPage = 50): array
    {
        $cacheKey = 'admin_manager_overview_'.md5(strtolower($search).'|'.$page.'|'.$perPage);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($search, $page, $perPage): array {
            $results = Manager::query()
                ->from('managers as m')
                ->leftJoin('leagues as l', 'l.id', '=', 'm.league_id')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($builder) use ($search): void {
                        $builder
                            ->where('m.team_name', 'like', "%{$search}%")
                            ->orWhere('m.player_name', 'like', "%{$search}%")
                            ->orWhere('l.name', 'like', "%{$search}%")
                            ->orWhere('m.entry_id', $search);
                    });
                })
                ->groupBy('m.entry_id')
                ->selectRaw('m.entry_id as entry_id')
                ->selectRaw('MAX(m.player_name) as player_name')
                ->selectRaw('MAX(m.team_name) as team_name')
                ->selectRaw('MAX(CASE WHEN m.user_id IS NULL THEN 0 ELSE 1 END) as is_claimed')
                ->selectRaw('MAX(m.claimed_at) as claimed_at')
                ->selectRaw('COUNT(DISTINCT m.league_id) as leagues_count')
                ->selectRaw("GROUP_CONCAT(DISTINCT l.name ORDER BY l.name SEPARATOR '|||') as league_names")
                ->orderByRaw('MAX(CASE WHEN m.user_id IS NULL THEN 0 ELSE 1 END) DESC')
                ->orderByRaw('MAX(m.claimed_at) DESC')
                ->orderByDesc('m.entry_id')
                ->paginate($perPage, ['*'], 'page', $page);

            $rows = collect($results->items())->map(function (object $row): array {
                $claimedAt = $row->claimed_at !== null
                    ? Carbon::parse((string) $row->claimed_at)
                    : null;

                $leagueNames = collect(explode('|||', (string) ($row->league_names ?? '')))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'entry_id' => (int) $row->entry_id,
                    'player_name' => (string) ($row->player_name ?? 'Unknown Manager'),
                    'team_name' => (string) ($row->team_name ?? 'Unknown Team'),
                    'is_claimed' => (bool) $row->is_claimed,
                    'claimed_at' => $claimedAt?->toIso8601String(),
                    'claimed_at_human' => $claimedAt?->diffForHumans(),
                    'leagues_count' => (int) $row->leagues_count,
                    'league_names' => $leagueNames,
                ];
            })->values()->all();

            return [
                'rows' => $rows,
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                ],
            ];
        });
    }
}
