<?php

namespace Tests\Feature;

use App\Models\FplPlayer;
use App\Models\FplTeam;
use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use App\Models\ManagerPick;
use App\Models\User;
use App\Services\LeagueStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LeagueTeamOfWeekTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_chooses_highest_scoring_valid_formation_for_team_of_week(): void
    {
        Cache::flush();
        $owner = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 88001,
            'name' => 'Formation League',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 990001,
            'player_name' => 'Formation Manager',
            'team_name' => 'Formation Team',
            'rank' => 1,
            'total_points' => 109,
        ]);

        FplTeam::create([
            'id' => 1,
            'name' => 'Team One',
            'short_name' => 'T1',
            'code' => 101,
            'strength_overall' => 4,
        ]);

        GameweekScore::create([
            'manager_id' => $manager->id,
            'gameweek' => 1,
            'season_year' => 2025,
            'points' => 109,
            'total_points' => 109,
            'overall_rank' => 1000,
        ]);

        $players = [
            ['id' => 1, 'name' => 'Goalkeeper One', 'type' => 1, 'points' => 6],
            ['id' => 2, 'name' => 'Defender One', 'type' => 2, 'points' => 10],
            ['id' => 3, 'name' => 'Defender Two', 'type' => 2, 'points' => 9],
            ['id' => 4, 'name' => 'Defender Three', 'type' => 2, 'points' => 8],
            ['id' => 5, 'name' => 'Defender Four', 'type' => 2, 'points' => 1],
            ['id' => 6, 'name' => 'Defender Five', 'type' => 2, 'points' => 1],
            ['id' => 7, 'name' => 'Midfielder One', 'type' => 3, 'points' => 7],
            ['id' => 8, 'name' => 'Midfielder Two', 'type' => 3, 'points' => 6],
            ['id' => 9, 'name' => 'Midfielder Three', 'type' => 3, 'points' => 5],
            ['id' => 10, 'name' => 'Midfielder Four', 'type' => 3, 'points' => 4],
            ['id' => 11, 'name' => 'Midfielder Five', 'type' => 3, 'points' => 3],
            ['id' => 12, 'name' => 'Forward One', 'type' => 4, 'points' => 20],
            ['id' => 13, 'name' => 'Forward Two', 'type' => 4, 'points' => 18],
            ['id' => 14, 'name' => 'Forward Three', 'type' => 4, 'points' => 16],
        ];

        foreach ($players as $position => $player) {
            FplPlayer::create([
                'id' => $player['id'],
                'team_id' => 1,
                'first_name' => $player['name'],
                'second_name' => 'Player',
                'web_name' => $player['name'],
                'element_type' => $player['type'],
                'now_cost' => 50,
                'total_points' => $player['points'],
                'selected_by_percent' => 10,
                'form' => 5,
                'region' => 1,
            ]);

            ManagerPick::create([
                'manager_id' => $manager->id,
                'gameweek' => 1,
                'player_id' => $player['id'],
                'position' => $position + 1,
                'multiplier' => 1,
                'is_captain' => false,
                'is_vice_captain' => false,
                'event_points' => $player['points'],
            ]);
        }

        $stats = app(LeagueStatsService::class)->getLeagueStats($league);

        $this->assertNotEmpty($stats['teamOfWeekRows']);

        $teamOfWeek = $stats['teamOfWeekRows'][0];

        $this->assertSame('3-4-3', $teamOfWeek['formation']);
        $this->assertCount(1, $teamOfWeek['goalkeeper']);
        $this->assertCount(3, $teamOfWeek['defenders']);
        $this->assertCount(4, $teamOfWeek['midfielders']);
        $this->assertCount(3, $teamOfWeek['forwards']);
        $this->assertSame(129, $teamOfWeek['total_points']);

        $captain = collect($teamOfWeek['forwards'])
            ->firstWhere('web_name', 'Forward One');

        $this->assertNotNull($captain);
        $this->assertTrue((bool) ($captain['is_captain'] ?? false));
        $this->assertSame(40, (int) ($captain['points'] ?? 0));

        $this->assertContains('Forward One', collect($teamOfWeek['forwards'])->pluck('web_name')->all());
        $this->assertContains('Forward Two', collect($teamOfWeek['forwards'])->pluck('web_name')->all());
        $this->assertContains('Forward Three', collect($teamOfWeek['forwards'])->pluck('web_name')->all());
    }
}
