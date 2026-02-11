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

    public function test_admin_can_manually_verify_a_claimed_manager_profile(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $claimer = User::factory()->create();
        $primaryLeague = $this->createLeague();
        $secondaryLeague = $this->createLeague();

        $primaryManager = Manager::create([
            'league_id' => $primaryLeague->id,
            'entry_id' => 202501,
            'player_name' => 'Manual Verify Candidate',
            'team_name' => 'Manual Verify Team',
            'rank' => 11,
            'total_points' => 701,
            'user_id' => $claimer->id,
            'claimed_at' => now()->subDay(),
        ]);

        $secondaryManager = Manager::create([
            'league_id' => $secondaryLeague->id,
            'entry_id' => 202501,
            'player_name' => 'Manual Verify Candidate',
            'team_name' => 'Manual Verify Team',
            'rank' => 14,
            'total_points' => 699,
            'user_id' => $claimer->id,
            'claimed_at' => now()->subDay(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.verifications.managers.verify', $primaryManager));

        $response
            ->assertRedirect()
            ->assertSessionHas('status', 'Manager profile verified successfully.');

        $this->assertDatabaseHas('managers', [
            'id' => $primaryManager->id,
            'verified_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('managers', [
            'id' => $secondaryManager->id,
            'verified_by' => $admin->id,
        ]);
    }

    public function test_admin_can_revoke_manual_manager_verification(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $claimer = User::factory()->create();
        $league = $this->createLeague();

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 202502,
            'player_name' => 'Manual Revoke Candidate',
            'team_name' => 'Manual Revoke Team',
            'rank' => 9,
            'total_points' => 725,
            'user_id' => $claimer->id,
            'claimed_at' => now()->subHours(8),
            'verified_at' => now()->subHours(2),
            'verified_by' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.verifications.managers.revoke', $manager));

        $response
            ->assertRedirect()
            ->assertSessionHas('status', 'Manager verification revoked successfully.');

        $this->assertDatabaseHas('managers', [
            'id' => $manager->id,
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }

    public function test_manual_verification_rejects_unclaimed_manager(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $league = $this->createLeague();

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 202503,
            'player_name' => 'Unclaimed Candidate',
            'team_name' => 'Unclaimed Team',
            'rank' => 16,
            'total_points' => 610,
            'user_id' => null,
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.verifications.managers.verify', $manager));

        $response
            ->assertRedirect()
            ->assertSessionHasErrors([
                'verification' => 'Only claimed profiles can be verified.',
            ]);

        $this->assertDatabaseHas('managers', [
            'id' => $manager->id,
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }

    public function test_admin_can_search_manual_verification_profiles(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $claimer = User::factory()->create([
            'name' => 'Searchable Claimer',
            'email' => 'searchable@example.com',
        ]);

        $league = $this->createLeague();

        Manager::create([
            'league_id' => $league->id,
            'entry_id' => 202504,
            'player_name' => 'Search Target Manager',
            'team_name' => 'Search Target Team',
            'rank' => 20,
            'total_points' => 550,
            'user_id' => $claimer->id,
            'claimed_at' => now()->subDays(2),
        ]);

        Manager::create([
            'league_id' => $league->id,
            'entry_id' => 202505,
            'player_name' => 'Non Claimed Manager',
            'team_name' => 'Should Not Appear',
            'rank' => 21,
            'total_points' => 542,
            'user_id' => null,
        ]);

        $this
            ->actingAs($admin)
            ->get(route('admin.verifications.index', [
                'status' => 'all',
                'manager_search' => 'Search Target',
            ]))
            ->assertOk()
            ->assertSee('Search Target Team')
            ->assertSee('Searchable Claimer')
            ->assertDontSee('Should Not Appear');
    }

    private function createPendingSubmission(): ProfileVerificationSubmission
    {
        $leagueOwner = User::factory()->create();
        $claimer = User::factory()->create();

        $league = $this->createLeague($leagueOwner->id);

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

    private function createLeague(?int $userId = null): League
    {
        static $leagueId = 9900;
        $leagueId++;

        $ownerId = $userId ?? User::factory()->create()->id;

        return League::create([
            'user_id' => $ownerId,
            'league_id' => $leagueId,
            'name' => 'Admin Verification League '.$leagueId,
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);
    }
}
