<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\FetchFplDataJob;
use App\Jobs\FetchManagerProfilesJob;
use App\Models\Manager;
use App\Models\ProfileVerificationSubmission;
use App\Services\ProfileStatsService;
use App\Services\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileStatsService $profileStatsService) {}

    public function index(Request $request): View
    {
        app(SeoService::class)->setProfileDashboard();

        $user = $request->user();
        $claimedManagers = $user->claimedManagers()
            ->with('favouriteTeam')
            ->orderByDesc('claimed_at')
            ->get()
            ->unique('entry_id')
            ->values();

        $selectedManager = null;

        if ($claimedManagers->isNotEmpty()) {
            $selectedManager = $claimedManagers
                ->firstWhere('id', (int) $request->query('manager'))
                ?? $claimedManagers->first();
        }

        $stats = null;
        $profileSuspended = false;
        $latestProfileVerificationSubmission = null;
        $profileVerificationState = null;

        if ($selectedManager !== null) {
            $profileSuspended = $selectedManager->isSuspended();
            $latestProfileVerificationSubmission = ProfileVerificationSubmission::query()
                ->where('user_id', $user->id)
                ->where('entry_id', $selectedManager->entry_id)
                ->latest('id')
                ->first();

            if ($selectedManager->isVerified()) {
                $profileVerificationState = 'verified';
            } elseif ($latestProfileVerificationSubmission?->status === 'pending') {
                $profileVerificationState = 'pending';
            } elseif ($latestProfileVerificationSubmission?->status === 'rejected') {
                $profileVerificationState = 'rejected';
            } else {
                $profileVerificationState = 'unverified';
            }

            if (! $profileSuspended) {
                $stats = $this->profileStatsService->getProfileStats($selectedManager);
            }
        }

        return view('profile.index', [
            'claimedManagers' => $claimedManagers,
            'selectedManager' => $selectedManager,
            'stats' => $stats,
            'profileSuspended' => $profileSuspended,
            'profileVerificationState' => $profileVerificationState,
            'latestProfileVerificationSubmission' => $latestProfileVerificationSubmission,
        ]);
    }

    public function search(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasClaimedProfile()) {
            return redirect()
                ->route('profile.index')
                ->with('status', 'You already claimed a profile. Unclaim first to search another one.');
        }

        app(SeoService::class)->setClaimSearch();

        return view('profile.search');
    }

    public function searchResults(Request $request): JsonResponse
    {
        if ($request->user()->hasClaimedProfile()) {
            return response()->json([
                'data' => [],
                'message' => 'You already claimed a profile.',
            ], 403);
        }

        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $cacheKey = 'profile_search_live_'.md5(mb_strtolower($query));

        $payload = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($query): array {
            $results = Manager::query()
                ->with(['latestGameweekScore', 'user:id,name'])
                ->where(function ($builder) use ($query): void {
                    $builder
                        ->where('team_name', 'like', "%{$query}%")
                        ->orWhere('player_name', 'like', "%{$query}%")
                        ->orWhereRaw("CONCAT(player_first_name, ' ', player_last_name) like ?", ["%{$query}%"])
                        ->orWhere('entry_id', $query);
                })
                ->orderByDesc('total_points')
                ->limit(250)
                ->get()
                ->unique('entry_id')
                ->values()
                ->take(30);

            $claimedByEntry = Manager::query()
                ->whereIn('entry_id', $results->pluck('entry_id')->all())
                ->whereNotNull('user_id')
                ->selectRaw('entry_id, MIN(user_id) as claimed_user_id')
                ->groupBy('entry_id')
                ->pluck('claimed_user_id', 'entry_id');

            return $results->map(function (Manager $manager) use ($claimedByEntry): array {
                return [
                    'id' => $manager->id,
                    'entry_id' => (int) $manager->entry_id,
                    'team_name' => $manager->team_name,
                    'player_name' => $manager->player_name,
                    'total_points' => (int) ($manager->total_points ?? 0),
                    'rank' => (int) ($manager->rank ?? 0),
                    'overall_rank' => (int) ($manager->latestGameweekScore?->overall_rank ?? 0),
                    'claimed_user_id' => $claimedByEntry[(int) $manager->entry_id] ?? null,
                ];
            })->values()->all();
        });

        return response()->json([
            'data' => $payload,
        ]);
    }

    public function claim(Request $request, Manager $manager): RedirectResponse
    {
        $currentEntryId = $request->user()
            ->claimedManagers()
            ->whereNotNull('user_id')
            ->value('entry_id');

        $entryId = $manager->entry_id;

        if ($currentEntryId !== null && (int) $currentEntryId !== (int) $entryId) {
            return Redirect::route('profile.index')->withErrors([
                'claim' => 'You can only claim one profile at a time. Unclaim your current profile first.',
            ]);
        }

        $isClaimedByAnotherUser = Manager::query()
            ->where('entry_id', $entryId)
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $request->user()->id)
            ->exists();

        if ($isClaimedByAnotherUser) {
            return Redirect::back()->withErrors([
                'claim' => 'This team has already been claimed by another user.',
            ]);
        }

        $managerIds = Manager::query()
            ->where('entry_id', $entryId)
            ->pluck('id')
            ->all();

        Manager::query()
            ->where('user_id', $request->user()->id)
            ->where('entry_id', '!=', $entryId)
            ->update([
                'user_id' => null,
                'claimed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
            ]);

        Manager::query()
            ->whereIn('id', $managerIds)
            ->update([
                'user_id' => $request->user()->id,
                'claimed_at' => now(),
                'verified_at' => null,
                'verified_by' => null,
                'sync_status' => 'processing',
                'sync_message' => 'Queued profile sync...',
                'suspended_at' => null,
            ]);

        foreach ($managerIds as $managerId) {
            Cache::forget('profile_stats_'.$managerId);
        }
        Cache::forget('profile_stats_entry_'.$entryId);

        FetchFplDataJob::dispatch();
        FetchManagerProfilesJob::dispatch($managerIds);

        return Redirect::route('profile.index')->with('status', 'Team claimed successfully. Syncing fresh profile data now.');
    }

    public function unclaim(Request $request, Manager $manager): RedirectResponse
    {
        $entryId = $manager->entry_id;

        Manager::query()
            ->where('entry_id', $entryId)
            ->where('user_id', $request->user()->id)
            ->update([
                'user_id' => null,
                'claimed_at' => null,
                'verified_at' => null,
                'verified_by' => null,
                'sync_status' => 'completed',
                'sync_message' => null,
                'suspended_at' => null,
            ]);

        Cache::forget('profile_stats_entry_'.$entryId);

        return Redirect::route('profile.index')->with('status', 'Team claim removed.');
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.index')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
