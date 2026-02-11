<?php

namespace Tests\Feature;

use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueShowMostValuableTeamsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_league_show_displays_most_valuable_teams_only_for_viewed_league(): void
    {
        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();

        $leagueA = League::create([
            'user_id' => $ownerA->id,
            'league_id' => 8801,
            'name' => 'Viewed League',
            'admin_name' => 'Owner A',
            'current_gameweek' => 2,
            'season' => 2025,
        ]);

        $leagueB = League::create([
            'user_id' => $ownerB->id,
            'league_id' => 8802,
            'name' => 'Other League',
            'admin_name' => 'Owner B',
            'current_gameweek' => 2,
            'season' => 2025,
        ]);

        $managerA = Manager::create([
            'league_id' => $leagueA->id,
            'entry_id' => 991001,
            'player_name' => 'Viewed Manager',
            'team_name' => 'Viewed Team',
            'rank' => 1,
            'total_points' => 150,
        ]);

        $managerB = Manager::create([
            'league_id' => $leagueB->id,
            'entry_id' => 991002,
            'player_name' => 'Other Manager',
            'team_name' => 'Other Team',
            'rank' => 1,
            'total_points' => 150,
        ]);

        GameweekScore::create([
            'manager_id' => $managerA->id,
            'gameweek' => 1,
            'season_year' => 2025,
            'points' => 70,
            'total_points' => 70,
            'value' => 1080,
        ]);

        GameweekScore::create([
            'manager_id' => $managerA->id,
            'gameweek' => 2,
            'season_year' => 2025,
            'points' => 80,
            'total_points' => 150,
            'value' => 1090,
        ]);

        GameweekScore::create([
            'manager_id' => $managerB->id,
            'gameweek' => 1,
            'season_year' => 2025,
            'points' => 70,
            'total_points' => 70,
            'value' => 1300,
        ]);

        GameweekScore::create([
            'manager_id' => $managerB->id,
            'gameweek' => 2,
            'season_year' => 2025,
            'points' => 80,
            'total_points' => 150,
            'value' => 1320,
        ]);

        $response = $this->get(route('public.leagues.show', ['slug' => $leagueA->slug]));

        $response
            ->assertOk()
            ->assertSee('MOST VALUABLE TEAMS')
            ->assertSee('Viewed Team')
            ->assertDontSee('Other Team');
    }

    public function test_public_league_overview_and_performance_tabs_show_separate_content(): void
    {
        $owner = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 9901,
            'name' => 'Tabbed League',
            'admin_name' => 'Owner',
            'current_gameweek' => 2,
            'season' => 2025,
        ]);

        $managerA = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 772001,
            'player_name' => 'Alpha Manager',
            'team_name' => 'Alpha Team',
            'rank' => 1,
            'total_points' => 150,
        ]);

        $managerB = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 772002,
            'player_name' => 'Beta Manager',
            'team_name' => 'Beta Team',
            'rank' => 2,
            'total_points' => 120,
        ]);

        GameweekScore::create([
            'manager_id' => $managerA->id,
            'gameweek' => 1,
            'season_year' => 2025,
            'points' => 80,
            'total_points' => 80,
            'value' => 1050,
        ]);

        GameweekScore::create([
            'manager_id' => $managerB->id,
            'gameweek' => 1,
            'season_year' => 2025,
            'points' => 60,
            'total_points' => 60,
            'value' => 1030,
        ]);

        GameweekScore::create([
            'manager_id' => $managerA->id,
            'gameweek' => 2,
            'season_year' => 2025,
            'points' => 70,
            'total_points' => 150,
            'value' => 1070,
        ]);

        GameweekScore::create([
            'manager_id' => $managerB->id,
            'gameweek' => 2,
            'season_year' => 2025,
            'points' => 60,
            'total_points' => 120,
            'value' => 1040,
        ]);

        $overviewResponse = $this->get(route('public.leagues.show', ['slug' => $league->slug]));

        $overviewResponse
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Performance')
            ->assertSee('MOST GW LEADS')
            ->assertDontSee('Gameweek Performance');

        $performanceResponse = $this->get(route('public.leagues.performance', ['slug' => $league->slug]));

        $performanceResponse
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Performance')
            ->assertSee('Gameweek Performance')
            ->assertSee('Jump to Gameweek');
    }
}
