<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardLeagueFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_claimed_profile_league_when_user_has_no_owned_league(): void
    {
        $user = User::factory()->create();
        $leagueOwner = User::factory()->create();

        $fallbackLeague = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 6501,
            'name' => 'Fallback League',
            'admin_name' => 'Owner',
            'current_gameweek' => 3,
            'season' => 2025,
        ]);

        Manager::create([
            'league_id' => $fallbackLeague->id,
            'entry_id' => 900001,
            'player_name' => 'Claimed Manager',
            'team_name' => 'Claimed Team',
            'rank' => 1,
            'total_points' => 120,
            'user_id' => $user->id,
            'claimed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('leagues.dashboard');
        $response->assertViewHas('league', fn (League $league): bool => $league->is($fallbackLeague));
        $response->assertViewHas('isOwnedLeague', false);
    }

    public function test_dashboard_empty_is_shown_when_user_has_no_owned_or_claimed_league(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('leagues.dashboard-empty');
    }
}
