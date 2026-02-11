<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResolveProfileVerificationSubmissionRequest;
use App\Models\Manager;
use App\Models\ProfileVerificationSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminProfileVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');
        $managerSearch = trim((string) $request->query('manager_search', ''));

        $submissions = ProfileVerificationSubmission::query()
            ->with(['user:id,name,email', 'reviewer:id,name'])
            ->when($status !== '' && $status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'rejected' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $pendingCount = ProfileVerificationSubmission::query()
            ->where('status', 'pending')
            ->count();

        $rejectedCount = ProfileVerificationSubmission::query()
            ->where('status', 'rejected')
            ->count();

        $approvedCount = ProfileVerificationSubmission::query()
            ->where('status', 'approved')
            ->count();

        $manualManagers = $this->manualManagersForSearch($managerSearch);

        return view('admin.verifications.index', [
            'submissions' => $submissions,
            'status' => $status,
            'pendingCount' => $pendingCount,
            'rejectedCount' => $rejectedCount,
            'approvedCount' => $approvedCount,
            'managerSearch' => $managerSearch,
            'manualManagers' => $manualManagers,
        ]);
    }

    public function resolve(
        ResolveProfileVerificationSubmissionRequest $request,
        ProfileVerificationSubmission $submission
    ): RedirectResponse {
        $action = $request->validated('action');

        if ($submission->status !== 'pending') {
            return back()->withErrors([
                'verification' => 'Only pending submissions can be reviewed.',
            ]);
        }

        if ($action === 'approve') {
            $affectedRows = Manager::query()
                ->where('entry_id', $submission->entry_id)
                ->where('user_id', $submission->user_id)
                ->update([
                    'verified_at' => now(),
                    'verified_by' => $request->user()->id,
                ]);

            if ($affectedRows === 0) {
                return back()->withErrors([
                    'verification' => 'This team is no longer claimed by the submitting user. Approval was not applied.',
                ]);
            }

            if ($submission->screenshot_path !== null && Storage::disk('local')->exists($submission->screenshot_path)) {
                Storage::disk('local')->delete($submission->screenshot_path);
            }

            $submission->update([
                'status' => 'approved',
                'rejection_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'approved_at' => now(),
                'screenshot_path' => null,
            ]);

            return back()->with('status', 'Profile verification approved and badge applied.');
        }

        $submission->update([
            'status' => 'rejected',
            'rejection_reason' => trim((string) $request->validated('rejection_reason')),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'approved_at' => null,
        ]);

        return back()->with('status', 'Profile verification rejected. User can submit a new request.');
    }

    public function screenshot(ProfileVerificationSubmission $submission): StreamedResponse
    {
        abort_if($submission->screenshot_path === null, 404);
        abort_unless(Storage::disk('local')->exists($submission->screenshot_path), 404);

        return Storage::disk('local')->response($submission->screenshot_path);
    }

    public function searchManagers(Request $request): View
    {
        $managerSearch = trim((string) $request->query('q', ''));
        $manualManagers = $this->manualManagersForSearch($managerSearch);

        return view('admin.verifications.partials.manual-managers-results', [
            'managerSearch' => $managerSearch,
            'manualManagers' => $manualManagers,
        ]);
    }

    public function verifyManager(Request $request, Manager $manager): RedirectResponse
    {
        if ($manager->user_id === null) {
            return back()->withErrors([
                'verification' => 'Only claimed profiles can be verified.',
            ]);
        }

        $affectedRows = Manager::query()
            ->where('entry_id', $manager->entry_id)
            ->whereNotNull('user_id')
            ->update([
                'verified_at' => now(),
                'verified_by' => $request->user()->id,
            ]);

        if ($affectedRows === 0) {
            return back()->withErrors([
                'verification' => 'Verification could not be applied. Please refresh and try again.',
            ]);
        }

        return back()->with('status', 'Manager profile verified successfully.');
    }

    public function revokeManagerVerification(Manager $manager): RedirectResponse
    {
        $affectedRows = Manager::query()
            ->where('entry_id', $manager->entry_id)
            ->whereNotNull('user_id')
            ->update([
                'verified_at' => null,
                'verified_by' => null,
            ]);

        if ($affectedRows === 0) {
            return back()->withErrors([
                'verification' => 'No claimed manager rows found to revoke.',
            ]);
        }

        return back()->with('status', 'Manager verification revoked successfully.');
    }

    private function manualManagersForSearch(string $managerSearch): Collection
    {
        if ($managerSearch === '') {
            return collect();
        }

        $canonicalManagerIds = Manager::query()
            ->from('managers as m')
            ->whereNotNull('m.user_id')
            ->where(function ($query) use ($managerSearch): void {
                $query
                    ->where('m.team_name', 'like', "%{$managerSearch}%")
                    ->orWhere('m.player_name', 'like', "%{$managerSearch}%")
                    ->orWhere('m.entry_id', $managerSearch)
                    ->orWhereExists(function ($userQuery) use ($managerSearch): void {
                        $userQuery
                            ->selectRaw('1')
                            ->from('users')
                            ->whereColumn('users.id', 'm.user_id')
                            ->where(function ($credentials) use ($managerSearch): void {
                                $credentials
                                    ->where('users.name', 'like', "%{$managerSearch}%")
                                    ->orWhere('users.email', 'like', "%{$managerSearch}%");
                            });
                    });
            })
            ->groupBy('m.entry_id')
            ->selectRaw('MAX(m.id) as id')
            ->pluck('id');

        return Manager::query()
            ->with(['user:id,name,email', 'verifiedBy:id,name'])
            ->whereIn('id', $canonicalManagerIds->all())
            ->orderByDesc('claimed_at')
            ->limit(25)
            ->get();
    }
}
