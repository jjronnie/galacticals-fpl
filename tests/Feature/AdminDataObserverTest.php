<?php

namespace Tests\Feature;

use App\Models\FplPlayer;
use App\Models\FplTeam;
use App\Models\League;
use App\Models\Manager;
use App\Models\ManagerChip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDataObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_db_observer_page_with_chips(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $leagueOwner = User::factory()->create();

        $league = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 8101,
            'name' => 'Observer League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 98001,
            'player_name' => 'Observer Manager',
            'team_name' => 'Observer Team',
            'rank' => 1,
            'total_points' => 99,
        ]);

        ManagerChip::create([
            'manager_id' => $manager->id,
            'gameweek' => 5,
            'chip_name' => '3xc',
            'points_before' => 300,
            'points_after' => 355,
        ]);

        ManagerChip::create([
            'manager_id' => $manager->id,
            'gameweek' => 6,
            'chip_name' => 'bboost',
            'points_before' => 355,
            'points_after' => 380,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.data.observer'));

        $response
            ->assertOk()
            ->assertSee('FPL DB Observer')
            ->assertSee('Tripple Captain')
            ->assertSee('Bench Boost');
    }

    public function test_admin_can_view_teams_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        FplTeam::create([
            'id' => 101,
            'name' => 'Observer FC',
            'short_name' => 'OBS',
            'code' => 9101,
            'strength_overall' => 4,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.teams'));

        $response
            ->assertOk()
            ->assertSee('FPL Teams')
            ->assertSee('Observer FC');
    }

    public function test_admin_can_view_team_players_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $team = FplTeam::create([
            'id' => 102,
            'name' => 'Test FC',
            'short_name' => 'TFC',
            'code' => 9102,
            'strength_overall' => 3,
        ]);

        FplPlayer::create([
            'id' => 502,
            'team_id' => $team->id,
            'first_name' => 'Test',
            'second_name' => 'Player',
            'web_name' => 'TPlayer',
            'element_type' => 2,
            'now_cost' => 50,
            'total_points' => 80,
            'selected_by_percent' => 12.00,
            'form' => 5.50,
            'region' => 1,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.teams.players', $team->id));

        $response
            ->assertOk()
            ->assertSee('Test FC')
            ->assertSee('TPlayer');
    }

    public function test_non_admin_cannot_view_db_observer_page(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.data.observer'));

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_view_teams_page(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.teams'));

        $response->assertForbidden();
    }
}
