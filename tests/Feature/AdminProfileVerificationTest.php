<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\Manager;
use App\Models\ProfileVerificationSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProfileVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_submission_and_discard_uploaded_screenshot(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $verification = $this->createPendingSubmission();

        Storage::disk('local')->put((string) $verification->screenshot_path, 'image-content');

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.verifications.resolve', $verification), [
                'action' => 'approve',
            ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('status', 'Profile verification approved and badge applied.');

        $verification->refresh();

        $this->assertSame('approved', $verification->status);
        $this->assertNull($verification->screenshot_path);
        $this->assertNotNull($verification->approved_at);
        $this->assertSame($admin->id, $verification->reviewed_by);

        $manager = Manager::query()->findOrFail($verification->manager_id);

        $this->assertNotNull($manager->verified_at);
        $this->assertSame($admin->id, $manager->verified_by);

        Storage::disk('local')->assertMissing('verifications/profiles/admin-review-proof.png');
    }

    public function test_admin_can_reject_submission_with_reason_and_user_sees_the_message(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $verification = $this->createPendingSubmission();

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.verifications.resolve', $verification), [
                'action' => 'reject',
                'rejection_reason' => 'Team name is not clearly readable in the screenshot.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('profile_verification_submissions', [
            'id' => $verification->id,
            'status' => 'rejected',
            'rejection_reason' => 'Team name is not clearly readable in the screenshot.',
            'reviewed_by' => $admin->id,
        ]);

        $user = User::findOrFail($verification->user_id);

        $this
            ->actingAs($user)
            ->get(route('profile.index'))
            ->assertOk()
            ->assertSee('Verification Rejected')
            ->assertSee('Team name is not clearly readable in the screenshot.');
    }

    public function test_non_admin_user_cannot_access_admin_verifications_page(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('admin.verifications.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_verifications_index_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->createPendingSubmission();

        $this
            ->actingAs($admin)
            ->get(route('admin.verifications.index'))
            ->assertOk()
            ->assertSee('Profile Verifications')
            ->assertSee('Admin Review Team');
    }

    private function createPendingSubmission(): ProfileVerificationSubmission
    {
        $leagueOwner = User::factory()->create();
        $claimer = User::factory()->create();

        $league = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 9901,
            'name' => 'Admin Verification League',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 778899,
            'player_name' => 'Admin Review Candidate',
            'team_name' => 'Admin Review Team',
            'rank' => 4,
            'total_points' => 95,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
        ]);

        return ProfileVerificationSubmission::query()->create([
            'user_id' => $claimer->id,
            'manager_id' => $manager->id,
            'entry_id' => $manager->entry_id,
            'team_name' => $manager->team_name,
            'player_name' => $manager->player_name,
            'screenshot_path' => 'verifications/profiles/admin-review-proof.png',
            'notes' => 'Please verify this screenshot.',
            'status' => 'pending',
        ]);
    }
}
