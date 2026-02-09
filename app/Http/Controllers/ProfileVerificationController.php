<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileVerificationSubmissionRequest;
use App\Models\Manager;
use App\Models\ProfileVerificationSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileVerificationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $claimedManager = $user->claimedManagers()
            ->whereNotNull('user_id')
            ->orderByDesc('claimed_at')
            ->first();

        if ($claimedManager === null) {
            return redirect()
                ->route('profile.index')
                ->withErrors(['verification' => 'Claim a profile first before submitting verification evidence.']);
        }

        $isAlreadyVerified = Manager::query()
            ->where('entry_id', $claimedManager->entry_id)
            ->where('user_id', $user->id)
            ->whereNotNull('verified_at')
            ->exists();

        if ($isAlreadyVerified) {
            return redirect()
                ->route('profile.index')
                ->with('status', 'Your profile is already verified.');
        }

        $latestSubmission = ProfileVerificationSubmission::query()
            ->where('user_id', $user->id)
            ->where('entry_id', $claimedManager->entry_id)
            ->latest('id')
            ->first();

        return view('profile.verification', [
            'claimedManager' => $claimedManager,
            'latestSubmission' => $latestSubmission,
        ]);
    }

    public function store(StoreProfileVerificationSubmissionRequest $request): RedirectResponse
    {
        $user = $request->user();
        $claimedManager = $user->claimedManagers()
            ->whereNotNull('user_id')
            ->orderByDesc('claimed_at')
            ->first();

        if ($claimedManager === null) {
            return redirect()
                ->route('profile.index')
                ->withErrors(['verification' => 'Claim a profile first before requesting verification.']);
        }

        $isAlreadyVerified = Manager::query()
            ->where('entry_id', $claimedManager->entry_id)
            ->where('user_id', $user->id)
            ->whereNotNull('verified_at')
            ->exists();

        if ($isAlreadyVerified) {
            return redirect()
                ->route('profile.index')
                ->with('status', 'Your profile is already verified.');
        }

        $hasPendingSubmission = ProfileVerificationSubmission::query()
            ->where('user_id', $user->id)
            ->where('entry_id', $claimedManager->entry_id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingSubmission) {
            return redirect()
                ->route('profile.index')
                ->with('status', 'Verification already submitted. Please wait for admin review.');
        }

        $screenshotPath = $request->file('screenshot')->store('verifications/profiles', 'local');

        ProfileVerificationSubmission::query()->create([
            'user_id' => $user->id,
            'manager_id' => $claimedManager->id,
            'entry_id' => $claimedManager->entry_id,
            'team_name' => $claimedManager->team_name,
            'player_name' => $claimedManager->player_name,
            'screenshot_path' => $screenshotPath,
            'notes' => $request->validated('notes'),
            'status' => 'pending',
        ]);

        return redirect()
            ->route('profile.index')
            ->with('status', 'Verification submitted successfully. Please await admin review.');
    }
}
