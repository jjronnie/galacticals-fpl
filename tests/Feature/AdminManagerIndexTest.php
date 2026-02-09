<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_claimed_profiles_page_lists_each_claimed_entry_once(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $claimer = User::factory()->create();
        $leagueOwner = User::factory()->create();

        $leagueA = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 9001,
            'name' => 'League A',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $leagueB = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 9002,
            'name' => 'League B',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        Manager::create([
            'league_id' => $leagueA->id,
            'entry_id' => 999,
            'player_name' => 'Duplicated Manager A',
            'team_name' => 'Duplicated Team',
            'rank' => 1,
            'total_points' => 120,
            'user_id' => $claimer->id,
            'claimed_at' => now()->subMinute(),
        ]);

        Manager::create([
            'league_id' => $leagueB->id,
            'entry_id' => 999,
            'player_name' => 'Duplicated Manager B',
            'team_name' => 'Duplicated Team',
            'rank' => 1,
            'total_points' => 121,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
        ]);

        Manager::create([
            'league_id' => $leagueA->id,
            'entry_id' => 1000,
            'player_name' => 'Single Manager',
            'team_name' => 'Single Team',
            'rank' => 2,
            'total_points' => 110,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.managers.index'));

        $response
            ->assertOk()
            ->assertSee('Entry 999')
            ->assertSee('Entry 1000');

        $this->assertSame(1, substr_count($response->getContent(), 'Entry 999'));
    }

    public function test_admin_can_unsuspend_a_claimed_profile_for_all_matching_entry_rows(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $claimer = User::factory()->create();
        $leagueOwner = User::factory()->create();

        $leagueA = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 9101,
            'name' => 'Unsuspend League A',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $leagueB = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 9102,
            'name' => 'Unsuspend League B',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $leagueA->id,
            'entry_id' => 445566,
            'player_name' => 'Suspended Manager A',
            'team_name' => 'Suspended Team',
            'rank' => 1,
            'total_points' => 130,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
            'suspended_at' => now(),
        ]);

        Manager::create([
            'league_id' => $leagueB->id,
            'entry_id' => 445566,
            'player_name' => 'Suspended Manager B',
            'team_name' => 'Suspended Team',
            'rank' => 2,
            'total_points' => 125,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
            'suspended_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.managers.unsuspend', $manager), [
                'reason' => 'Ownership verified after review.',
            ]);

        $response->assertRedirect();

        $this->assertSame(
            2,
            Manager::query()
                ->where('entry_id', 445566)
                ->whereNull('suspended_at')
                ->count()
        );

        $this->assertDatabaseHas('managers', [
            'entry_id' => 445566,
            'notes' => 'Ownership verified after review.',
        ]);
    }
}
