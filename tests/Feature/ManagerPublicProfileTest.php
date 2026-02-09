<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_manager_profile_uses_entry_id_route(): void
    {
        $owner = User::factory()->create();
        $claimer = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 7001,
            'name' => 'Public Profile League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 36647,
            'player_name' => 'Public Manager',
            'team_name' => 'Public Team',
            'rank' => 1,
            'total_points' => 150,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
        ]);

        $response = $this->get(route('managers.show', ['entryId' => $manager->entry_id]));

        $response
            ->assertOk()
            ->assertSee('Public Team')
            ->assertSee((string) $manager->entry_id)
            ->assertSee('Claimed Profile')
            ->assertSee('Login to Report')
            ->assertDontSee('This profile is verified because they confirmed ownership of the team.');
    }

    public function test_verified_claimed_public_profile_shows_verified_badge_message(): void
    {
        $owner = User::factory()->create();
        $claimer = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 7006,
            'name' => 'Verified Public Profile League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 888222,
            'player_name' => 'Verified Public Manager',
            'team_name' => 'Verified Public Team',
            'rank' => 2,
            'total_points' => 140,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
            'verified_at' => now(),
        ]);

        $this->get(route('managers.show', ['entryId' => $manager->entry_id]))
            ->assertOk()
            ->assertSee('Verified Public Team')
            ->assertSee('This profile is verified because they confirmed ownership of the team.')
            ->assertDontSee('Claim Complaint')
            ->assertDontSee('Login to Report');
    }

    public function test_short_profile_code_redirects_to_entry_id_url(): void
    {
        $owner = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 7002,
            'name' => 'Short Profile League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 555000,
            'player_name' => 'Short Code Manager',
            'team_name' => 'Short Code Team',
            'rank' => 2,
            'total_points' => 100,
        ]);

        $code = strtoupper(base_convert((string) $manager->entry_id, 10, 36));

        $response = $this->get(route('managers.short', ['code' => $code]));

        $response->assertRedirect(route('managers.show', ['entryId' => $manager->entry_id]));
    }

    public function test_suspended_public_profile_is_hidden(): void
    {
        $owner = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 7003,
            'name' => 'Suspended Profile League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        Manager::create([
            'league_id' => $league->id,
            'entry_id' => 123123,
            'player_name' => 'Suspended Manager',
            'team_name' => 'Suspended Team',
            'rank' => 5,
            'total_points' => 60,
            'suspended_at' => now(),
        ]);

        $response = $this->get(route('managers.show', ['entryId' => 123123]));

        $response->assertNotFound();
    }

    public function test_unclaimed_profile_shows_badge_and_claim_prompt_without_stats(): void
    {
        $owner = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 7004,
            'name' => 'Unclaimed Profile League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        Manager::create([
            'league_id' => $league->id,
            'entry_id' => 654321,
            'player_name' => 'Unclaimed Manager',
            'team_name' => 'Unclaimed Team',
            'rank' => 3,
            'total_points' => 80,
        ]);

        $response = $this->get(route('managers.show', ['entryId' => 654321]));

        $response
            ->assertOk()
            ->assertSee('Unclaimed Profile')
            ->assertSee('claim it from your account')
            ->assertDontSee('Total Points');
    }

    public function test_authenticated_user_sees_complaint_button_on_claimed_public_profile(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $claimer = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 7005,
            'name' => 'Complaint CTA League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 777123,
            'player_name' => 'Claimed Manager',
            'team_name' => 'Claimed Team',
            'rank' => 4,
            'total_points' => 88,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
        ]);

        $response = $this
            ->actingAs($viewer)
            ->get(route('managers.show', ['entryId' => $manager->entry_id]));

        $response
            ->assertOk()
            ->assertSee('Report Claimed Profile');
    }

    public function test_authenticated_user_does_not_see_complaint_button_on_verified_claimed_public_profile(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $claimer = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 7007,
            'name' => 'Verified Complaint Hidden League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 777124,
            'player_name' => 'Verified Claimed Manager',
            'team_name' => 'Verified Claimed Team',
            'rank' => 4,
            'total_points' => 88,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
            'verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($viewer)
            ->get(route('managers.show', ['entryId' => $manager->entry_id]));

        $response
            ->assertOk()
            ->assertDontSee('Report Claimed Profile')
            ->assertDontSee('Claim Complaint');
    }
}
