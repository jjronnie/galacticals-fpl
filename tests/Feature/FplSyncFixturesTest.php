<?php

namespace Tests\Feature;

use App\Models\FplFixture;
use App\Models\FplSyncRun;
use App\Models\FplTeam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FplSyncFixturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('fpl.current_event');
    }

    public function test_fixture_sync_command_upserts_fixtures_from_api(): void
    {
        Http::fake([
            '*/fixtures/*' => Http::response([
                [
                    'id' => 1,
                    'event' => 1,
                    'team_h' => 1,
                    'team_a' => 2,
                    'kickoff_time' => '2026-08-15T12:30:00Z',
                    'started' => false,
                    'finished' => false,
                    'finished_provisional' => false,
                    'team_h_score' => null,
                    'team_a_score' => null,
                    'minutes' => null,
                    'pulse_id' => 123,
                    'stats' => [],
                ],
                [
                    'id' => 2,
                    'event' => 1,
                    'team_h' => 3,
                    'team_a' => 4,
                    'kickoff_time' => '2026-08-15T15:00:00Z',
                    'started' => true,
                    'finished' => true,
                    'finished_provisional' => true,
                    'team_h_score' => 2,
                    'team_a_score' => 1,
                    'minutes' => 90,
                    'pulse_id' => 124,
                    'stats' => [],
                ],
            ], 200),
        ]);

        $this->artisan('fpl:sync-fixtures')
            ->assertSuccessful()
            ->expectsOutput('Upserted 2 fixtures successfully.');

        $this->assertDatabaseCount('fpl_fixtures', 2);
        $this->assertDatabaseHas('fpl_fixtures', [
            'fpl_fixture_id' => 1,
            'event' => 1,
            'team_h' => 1,
            'team_a' => 2,
        ]);
        $this->assertDatabaseHas('fpl_fixtures', [
            'fpl_fixture_id' => 2,
            'event' => 1,
            'finished' => true,
            'team_h_score' => 2,
            'team_a_score' => 1,
        ]);
    }

    public function test_fixture_sync_command_handles_api_failure(): void
    {
        Http::fake([
            '*/fixtures/*' => Http::response([], 500),
        ]);

        $this->artisan('fpl:sync-fixtures')
            ->assertFailed();
    }

    public function test_matchday_checker_exits_when_fixtures_not_finished(): void
    {
        FplTeam::create(['id' => 1, 'name' => 'Arsenal', 'short_name' => 'ARS', 'code' => 3, 'strength_overall' => 4]);
        FplTeam::create(['id' => 2, 'name' => 'Chelsea', 'short_name' => 'CHE', 'code' => 8, 'strength_overall' => 4]);

        FplFixture::create([
            'fpl_fixture_id' => 1,
            'event' => 5,
            'team_h' => 1,
            'team_a' => 2,
            'kickoff_time' => now()->subHours(2),
            'started' => true,
            'finished' => false,
            'finished_provisional' => false,
            'team_h_score' => null,
            'team_a_score' => null,
            'minutes' => 45,
        ]);

        $this->artisan('fpl:run-sync-if-matchday-complete')
            ->assertSuccessful()
            ->expectsOutput('Gameweek 5 not complete: 1 fixtures still unfinished.');
    }

    public function test_matchday_checker_exits_when_within_buffer_time(): void
    {
        FplTeam::create(['id' => 1, 'name' => 'Arsenal', 'short_name' => 'ARS', 'code' => 3, 'strength_overall' => 4]);
        FplTeam::create(['id' => 2, 'name' => 'Chelsea', 'short_name' => 'CHE', 'code' => 8, 'strength_overall' => 4]);

        FplFixture::create([
            'fpl_fixture_id' => 1,
            'event' => 5,
            'team_h' => 1,
            'team_a' => 2,
            'kickoff_time' => now()->subHours(2),
            'started' => true,
            'finished' => true,
            'finished_provisional' => true,
            'team_h_score' => 3,
            'team_a_score' => 1,
            'minutes' => 90,
        ]);

        $this->artisan('fpl:run-sync-if-matchday-complete')
            ->assertSuccessful()
            ->expectsOutputToContain('Waiting buffer time until');
    }

    public function test_matchday_checker_exits_when_already_synced(): void
    {
        FplTeam::create(['id' => 1, 'name' => 'Arsenal', 'short_name' => 'ARS', 'code' => 3, 'strength_overall' => 4]);
        FplTeam::create(['id' => 2, 'name' => 'Chelsea', 'short_name' => 'CHE', 'code' => 8, 'strength_overall' => 4]);

        FplFixture::create([
            'fpl_fixture_id' => 1,
            'event' => 5,
            'team_h' => 1,
            'team_a' => 2,
            'kickoff_time' => now()->subHours(10),
            'started' => true,
            'finished' => true,
            'finished_provisional' => true,
            'team_h_score' => 3,
            'team_a_score' => 1,
            'minutes' => 90,
        ]);

        FplSyncRun::create([
            'event' => 5,
            'status' => 'success',
            'synced_at' => now(),
        ]);

        $this->artisan('fpl:run-sync-if-matchday-complete')
            ->assertSuccessful()
            ->expectsOutput('Sync already executed for gameweek 5. Exiting.');
    }

    public function test_homepage_shows_fixtures_for_current_event(): void
    {
        Http::fake(['*/bootstrap-static/*' => Http::response(['events' => []], 404)]);

        FplTeam::create(['id' => 1, 'name' => 'Arsenal', 'short_name' => 'ARS', 'code' => 3, 'strength_overall' => 4]);
        FplTeam::create(['id' => 2, 'name' => 'Chelsea', 'short_name' => 'CHE', 'code' => 8, 'strength_overall' => 4]);

        FplFixture::create([
            'fpl_fixture_id' => 1,
            'event' => 10,
            'team_h' => 1,
            'team_a' => 2,
            'kickoff_time' => now()->addDays(2),
            'started' => false,
            'finished' => false,
            'finished_provisional' => false,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Gameweek 10')
            ->assertSee('ARS')
            ->assertSee('CHE');
    }

    public function test_homepage_shows_specific_event_when_requested(): void
    {
        Http::fake(['*/bootstrap-static/*' => Http::response(['events' => []], 404)]);

        FplTeam::create(['id' => 1, 'name' => 'Arsenal', 'short_name' => 'ARS', 'code' => 3, 'strength_overall' => 4]);
        FplTeam::create(['id' => 2, 'name' => 'Chelsea', 'short_name' => 'CHE', 'code' => 8, 'strength_overall' => 4]);

        FplFixture::create([
            'fpl_fixture_id' => 1,
            'event' => 5,
            'team_h' => 1,
            'team_a' => 2,
            'kickoff_time' => now()->subDays(10),
            'started' => true,
            'finished' => true,
            'finished_provisional' => true,
            'team_h_score' => 2,
            'team_a_score' => 1,
            'minutes' => 90,
        ]);

        FplFixture::create([
            'fpl_fixture_id' => 2,
            'event' => 10,
            'team_h' => 2,
            'team_a' => 1,
            'kickoff_time' => now()->addDays(2),
            'started' => false,
            'finished' => false,
            'finished_provisional' => false,
        ]);

        $response = $this->get('/?event=5');

        $response->assertStatus(200)
            ->assertSee('Gameweek 5')
            ->assertSee('2 - 1')
            ->assertSee('Full Time');
    }

    public function test_homepage_has_prev_and_next_buttons(): void
    {
        Http::fake(['*/bootstrap-static/*' => Http::response(['events' => []], 404)]);

        FplTeam::create(['id' => 1, 'name' => 'Arsenal', 'short_name' => 'ARS', 'code' => 3, 'strength_overall' => 4]);
        FplTeam::create(['id' => 2, 'name' => 'Chelsea', 'short_name' => 'CHE', 'code' => 8, 'strength_overall' => 4]);

        FplFixture::create([
            'fpl_fixture_id' => 1,
            'event' => 4,
            'team_h' => 1,
            'team_a' => 2,
            'kickoff_time' => now()->subDays(20),
            'started' => true,
            'finished' => true,
            'finished_provisional' => true,
            'team_h_score' => 1,
            'team_a_score' => 1,
            'minutes' => 90,
        ]);

        FplFixture::create([
            'fpl_fixture_id' => 2,
            'event' => 5,
            'team_h' => 2,
            'team_a' => 1,
            'kickoff_time' => now()->subDays(10),
            'started' => true,
            'finished' => true,
            'finished_provisional' => true,
            'team_h_score' => 3,
            'team_a_score' => 0,
            'minutes' => 90,
        ]);

        FplFixture::create([
            'fpl_fixture_id' => 3,
            'event' => 6,
            'team_h' => 1,
            'team_a' => 2,
            'kickoff_time' => now()->addDays(5),
            'started' => false,
            'finished' => false,
            'finished_provisional' => false,
        ]);

        $response = $this->get('/?event=5');

        $response->assertStatus(200)
            ->assertSee('GW 4')
            ->assertSee('GW 6');
    }
}
