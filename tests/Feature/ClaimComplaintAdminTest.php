<?php

namespace Tests\Feature;

use App\Mail\ClaimComplaintSubmittedMail;
use App\Models\ClaimsComplaint;
use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClaimComplaintAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_claim_complaint(): void
    {
        $leagueOwner = User::factory()->create();

        $league = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 6000,
            'name' => 'Guest Complaint League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 776,
            'player_name' => 'Guest Report Manager',
            'team_name' => 'Guest Report Team',
            'rank' => 1,
            'total_points' => 100,
        ]);

        $response = $this->post(route('profile.complaint', $manager), [
            'subject' => 'Wrong claim',
            'message' => 'This should be blocked because I am not authenticated.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('claims_complaints', 0);
    }

    public function test_authenticated_user_can_submit_claim_complaint_and_admin_can_resolve_it(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $reporter = User::factory()->create();
        $leagueOwner = User::factory()->create();

        $league = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 6001,
            'name' => 'Complaints League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 777,
            'player_name' => 'Reported Manager',
            'team_name' => 'Reported Team',
            'rank' => 1,
            'total_points' => 120,
        ]);

        $storeResponse = $this
            ->actingAs($reporter)
            ->post(route('profile.complaint', $manager), [
                'subject' => 'Wrong claim',
                'message' => 'I believe this team is claimed by the wrong user.',
            ]);

        $storeResponse->assertRedirect();

        $this->assertDatabaseHas('claims_complaints', [
            'manager_id' => $manager->id,
            'reporter_user_id' => $reporter->id,
            'subject' => 'Wrong claim',
            'status' => 'open',
        ]);

        Mail::assertQueued(ClaimComplaintSubmittedMail::class, function ($mail): bool {
            return $mail->hasTo('ronaldjjuuko7@gmail.com');
        });

        $complaint = ClaimsComplaint::firstOrFail();

        $adminIndexResponse = $this
            ->actingAs($admin)
            ->get(route('admin.complaints.index'));

        $adminIndexResponse
            ->assertOk()
            ->assertSee('Wrong claim');

        $resolveResponse = $this
            ->actingAs($admin)
            ->patch(route('admin.complaints.resolve', $complaint), [
                'status' => 'resolved',
            ]);

        $resolveResponse->assertRedirect();

        $this->assertDatabaseHas('claims_complaints', [
            'id' => $complaint->id,
            'status' => 'resolved',
            'resolved_by' => $admin->id,
        ]);
    }

    public function test_duplicate_complaints_are_blocked_for_the_same_day(): void
    {
        Mail::fake();

        $reporter = User::factory()->create();
        $leagueOwner = User::factory()->create();

        $league = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 6002,
            'name' => 'Duplicate Complaints League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 778,
            'player_name' => 'Duplicate Manager',
            'team_name' => 'Duplicate Team',
            'rank' => 2,
            'total_points' => 110,
        ]);

        $payload = [
            'subject' => 'Wrong claim',
            'message' => 'I believe this team is claimed by the wrong user.',
        ];

        $this
            ->actingAs($reporter)
            ->post(route('profile.complaint', $manager), $payload)
            ->assertRedirect();

        $duplicateResponse = $this
            ->actingAs($reporter)
            ->from(route('profile.search', ['q' => $manager->entry_id]))
            ->post(route('profile.complaint', $manager), $payload);

        $duplicateResponse
            ->assertRedirect(route('profile.search', ['q' => $manager->entry_id]))
            ->assertSessionHasErrors('complaint');

        $this->assertDatabaseCount('claims_complaints', 1);
    }
}
