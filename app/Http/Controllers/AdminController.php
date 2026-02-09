<?php

namespace App\Http\Controllers;

use App\Jobs\SendLeagueReminderJob;
use App\Models\ClaimsComplaint;
use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use App\Services\SyncJobProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with(['league' => function ($query): void {
                $query->withCount('managers');
            }])
            ->addSelect([
                'claimed_profile_entry_id' => Manager::query()
                    ->select('entry_id')
                    ->whereColumn('managers.user_id', 'users.id')
                    ->whereNotNull('managers.user_id')
                    ->orderByDesc('claimed_at')
                    ->orderByDesc('id')
                    ->limit(1),
                'claimed_profile_team_name' => Manager::query()
                    ->select('team_name')
                    ->whereColumn('managers.user_id', 'users.id')
                    ->whereNotNull('managers.user_id')
                    ->orderByDesc('claimed_at')
                    ->orderByDesc('id')
                    ->limit(1),
                'claimed_profile_player_name' => Manager::query()
                    ->select('player_name')
                    ->whereColumn('managers.user_id', 'users.id')
                    ->whereNotNull('managers.user_id')
                    ->orderByDesc('claimed_at')
                    ->orderByDesc('id')
                    ->limit(1),
                'claimed_profile_claimed_at' => Manager::query()
                    ->select('claimed_at')
                    ->whereColumn('managers.user_id', 'users.id')
                    ->whereNotNull('managers.user_id')
                    ->orderByDesc('claimed_at')
                    ->orderByDesc('id')
                    ->limit(1),
                'claimed_profiles_count' => Manager::query()
                    ->selectRaw('COUNT(DISTINCT entry_id)')
                    ->whereColumn('managers.user_id', 'users.id')
                    ->whereNotNull('managers.user_id'),
            ])
            ->orderByDesc('id')
            ->paginate(50);

        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $unverifiedUsers = User::whereNull('email_verified_at')->count();
        $totalLeagues = League::count();
        $totalManagers = Manager::count();
        $claimedManagers = Manager::query()
            ->whereNotNull('user_id')
            ->distinct('entry_id')
            ->count('entry_id');
        $suspendedProfiles = Manager::query()
            ->whereNotNull('suspended_at')
            ->distinct('entry_id')
            ->count('entry_id');
        $openComplaints = ClaimsComplaint::whereIn('status', ['open', 'in_progress'])->count();

        $usersBySignupMethod = User::query()
            ->select('signup_method', DB::raw('COUNT(*) as total'))
            ->groupBy('signup_method')
            ->pluck('total', 'signup_method');

        $managersPerLeague = League::withCount('managers')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.users.index', compact(
            'users',
            'totalUsers',
            'verifiedUsers',
            'unverifiedUsers',
            'usersBySignupMethod',
            'totalLeagues',
            'totalManagers',
            'managersPerLeague',
            'claimedManagers',
            'suspendedProfiles',
            'openComplaints'
        ));
    }

    public function sendMissingLeagueReminders(): RedirectResponse
    {
        SyncJobProgressService::queue(
            SyncJobProgressService::SEND_LEAGUE_REMINDERS,
            1,
            'League reminder job queued.'
        );

        SendLeagueReminderJob::dispatch();

        return back()->with('success', 'League reminder emails are being sent.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'status' => ['required', 'string', 'in:active,suspended'],
            'role' => ['required', 'string', 'in:user,admin'],
            'profile_photo_path' => ['nullable', 'image', 'max:2048'],
            'sync_status' => ['nullable', 'string', 'max:255'],
            'email_verified_at' => ['nullable', 'date'],
        ]);

        if ($user->league !== null) {
            $user->league->update([
                'sync_status' => $validated['sync_status'] ?? null,
            ]);
        }

        if ($request->hasFile('profile_photo_path')) {
            if ($user->profile_photo_path !== null && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $validated['profile_photo_path'] = $request->file('profile_photo_path')->store('avatars/users', 'public');
        }

        $user->update($validated);

        $user->email_verified_at = $request->filled('email_verified_at')
            ? $request->date('email_verified_at')
            : null;

        $user->save();

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return back()->with('error', 'Admin users cannot be deleted.');
        }

        $user->delete();

        return back()->with('status', 'User deleted successfully.');
    }
}
