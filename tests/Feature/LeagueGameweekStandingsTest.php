<?php

namespace Tests\Feature;

use App\Jobs\ComputeLeagueGameweekStandingsJob;
use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueGameweekStandingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_league_page_shows_cached_gameweek_table_when_gameweek_selected(): void
    {
        $owner = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 5001,
            'name' => 'GW League',
            'admin_name' => 'Admin',
            'current_gameweek' => 2,
            'season' => 2025,
        ]);

        $managerA = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 101,
            'player_name' => 'Alpha Manager',
            'team_name' => 'Alpha Team',
            'rank' => 1,
            'total_points' => 160,
        ]);

        $managerB = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 102,
            'player_name' => 'Beta Manager',
            'team_name' => 'Beta Team',
            'rank' => 2,
            'total_points' => 140,
        ]);

        GameweekScore::create([
            'manager_id' => $managerA->id,
            'gameweek' => 1,
            'season_year' => 2025,
            'points' => 80,
            'total_points' => 80,
        ]);

        GameweekScore::create([
            'manager_id' => $managerB->id,
            'gameweek' => 1,
            'season_year' => 2025,
            'points' => 55,
            'total_points' => 55,
        ]);

        GameweekScore::create([
            'manager_id' => $managerA->id,
            'gameweek' => 2,
            'season_year' => 2025,
            'points' => 80,
            'total_points' => 160,
        ]);

        GameweekScore::create([
            'manager_id' => $managerB->id,
            'gameweek' => 2,
            'season_year' => 2025,
            'points' => 85,
            'total_points' => 140,
        ]);

        ComputeLeagueGameweekStandingsJob::dispatchSync($league->id, 2025);

        $response = $this->get(route('public.leagues.gameweek.show', ['slug' => $league->slug, 'gameweek' => 1]));

        $response
            ->assertOk()
            ->assertSee('Gameweek 1 Overview')
            ->assertSee('Alpha Manager')
            ->assertSee('Beta Manager');
    }
}
