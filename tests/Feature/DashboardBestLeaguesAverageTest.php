<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use App\Services\DashboardStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardBestLeaguesAverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_best_leagues_use_full_league_average_not_top_five_average(): void
    {
        Cache::flush();

        $ownerA = User::factory()->create();
        $ownerB = User::factory()->create();

        $leagueA = League::create([
            'user_id' => $ownerA->id,
            'league_id' => 7101,
            'name' => 'Top Heavy League',
            'admin_name' => 'Owner A',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $leagueB = League::create([
            'user_id' => $ownerB->id,
            'league_id' => 7102,
            'name' => 'Consistent League',
            'admin_name' => 'Owner B',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        foreach ([120, 120, 120, 120, 120, 0] as $index => $points) {
            Manager::create([
                'league_id' => $leagueA->id,
                'entry_id' => 800000 + $index,
                'player_name' => 'Manager A'.$index,
                'team_name' => 'Team A'.$index,
                'rank' => $index + 1,
                'total_points' => $points,
            ]);
        }

        foreach ([110, 110, 110, 110, 110, 110] as $index => $points) {
            Manager::create([
                'league_id' => $leagueB->id,
                'entry_id' => 810000 + $index,
                'player_name' => 'Manager B'.$index,
                'team_name' => 'Team B'.$index,
                'rank' => $index + 1,
                'total_points' => $points,
            ]);
        }

        $bestLeagues = app(DashboardStatsService::class)->getGlobalDashboardStats()['best_leagues'] ?? [];

        $this->assertNotEmpty($bestLeagues);
        $this->assertSame('Consistent League', $bestLeagues[0]['league_name']);
        $this->assertSame(110.0, $bestLeagues[0]['average']);

        $topHeavyLeagueRow = collect($bestLeagues)
            ->firstWhere('league_name', 'Top Heavy League');

        $this->assertNotNull($topHeavyLeagueRow);
        $this->assertSame(100.0, $topHeavyLeagueRow['average']);
    }
}
