<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminManagerActionRequest;
use App\Models\Manager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminManagerController extends Controller
{
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
                'suspended_at' => null,
                'notes' => $request->validated('reason'),
            ]);

        return back()->with('status', 'Manager claim disbanded successfully.');
    }
}
