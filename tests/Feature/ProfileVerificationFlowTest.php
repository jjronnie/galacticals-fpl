<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\Manager;
use App\Models\ProfileVerificationSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_claimed_user_can_submit_profile_verification_screenshot(): void
    {
        Storage::fake('local');

        $claimedManager = $this->createClaimedManager();
        $user = User::findOrFail($claimedManager->user_id);

        $response = $this
            ->actingAs($user)
            ->post(route('profile.verification.store'), [
                'screenshot' => UploadedFile::fake()->image('fpl-proof.png', 1242, 2688),
                'notes' => 'I am logged in on the official app.',
            ]);

        $response
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('status', 'Verification submitted successfully. Please await admin review.');

        $submission = ProfileVerificationSubmission::query()->firstOrFail();

        $this->assertSame('pending', $submission->status);
        $this->assertSame($user->id, $submission->user_id);
        $this->assertSame($claimedManager->entry_id, (int) $submission->entry_id);
        $this->assertSame('I am logged in on the official app.', $submission->notes);

        Storage::disk('local')->assertExists((string) $submission->screenshot_path);
    }

    public function test_pending_submission_hides_submit_call_to_action_on_profile_dashboard(): void
    {
        Storage::fake('local');

        $claimedManager = $this->createClaimedManager();
        $user = User::findOrFail($claimedManager->user_id);

        Storage::disk('local')->put('verifications/profiles/pending-proof.png', 'image-content');

        ProfileVerificationSubmission::query()->create([
            'user_id' => $user->id,
            'manager_id' => $claimedManager->id,
            'entry_id' => $claimedManager->entry_id,
            'team_name' => $claimedManager->team_name,
            'player_name' => $claimedManager->player_name,
            'screenshot_path' => 'verifications/profiles/pending-proof.png',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('profile.index'));

        $response
            ->assertOk()
            ->assertSee('Verification Pending')
            ->assertDontSee('Submit Verification Evidence');
    }

    public function test_rejected_submission_message_is_visible_to_user_on_profile_dashboard(): void
    {
        $claimedManager = $this->createClaimedManager();
        $user = User::findOrFail($claimedManager->user_id);

        ProfileVerificationSubmission::query()->create([
            'user_id' => $user->id,
            'manager_id' => $claimedManager->id,
            'entry_id' => $claimedManager->entry_id,
            'team_name' => $claimedManager->team_name,
            'player_name' => $claimedManager->player_name,
            'screenshot_path' => 'verifications/profiles/rejected-proof.png',
            'status' => 'rejected',
            'rejection_reason' => 'Team name is blurred. Submit a clearer screenshot.',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('profile.index'));

        $response
            ->assertOk()
            ->assertSee('Verification Rejected')
            ->assertSee('Team name is blurred. Submit a clearer screenshot.')
            ->assertSee('Retry Verification');
    }

    private function createClaimedManager(): Manager
    {
        $leagueOwner = User::factory()->create();
        $claimer = User::factory()->create();

        $league = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 8801,
            'name' => 'Verification League',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        return Manager::create([
            'league_id' => $league->id,
            'entry_id' => 901122,
            'player_name' => 'Verified Candidate',
            'team_name' => 'Verification Squad',
            'rank' => 2,
            'total_points' => 102,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
        ]);
    }
}
