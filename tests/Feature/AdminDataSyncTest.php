<?php

namespace Tests\Feature;

use App\Jobs\ComputeLeagueGameweekStandingsJob;
use App\Jobs\FetchFplDataJob;
use App\Jobs\FetchLeagueStandings;
use App\Jobs\FetchManagerProfilesJob;
use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use App\Services\SyncJobProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AdminDataSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_queue_full_application_sync_from_data_panel(): void
    {
        Bus::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $leagueOwner = User::factory()->create();
        $claimer = User::factory()->create();

        $leagueA = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 5101,
            'name' => 'Sync League A',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $leagueB = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 5102,
            'name' => 'Sync League B',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        Manager::create([
            'league_id' => $leagueA->id,
            'entry_id' => 101010,
            'player_name' => 'Claimed Manager A',
            'team_name' => 'Claimed Team A',
            'rank' => 1,
            'total_points' => 100,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
        ]);

        Manager::create([
            'league_id' => $leagueB->id,
            'entry_id' => 101011,
            'player_name' => 'Claimed Manager B',
            'team_name' => 'Claimed Team B',
            'rank' => 2,
            'total_points' => 95,
            'user_id' => $claimer->id,
            'claimed_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->postJson(route('admin.data.syncAll'));

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Full application data update queued successfully.')
            ->assertJsonPath('payload.summary.total_leagues', 2)
            ->assertJsonPath('payload.summary.claimed_managers', 2);

        Bus::assertChained([
            FetchFplDataJob::class,
            FetchLeagueStandings::class,
            FetchLeagueStandings::class,
            FetchManagerProfilesJob::class,
            ComputeLeagueGameweekStandingsJob::class,
            ComputeLeagueGameweekStandingsJob::class,
        ]);
    }

    public function test_admin_data_status_endpoint_returns_job_and_league_progress_payload(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $leagueOwner = User::factory()->create();

        League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 5201,
            'name' => 'Status League',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
            'sync_status' => 'processing',
            'total_managers' => 20,
            'synced_managers' => 5,
            'sync_message' => 'Processing managers',
        ]);

        SyncJobProgressService::queue(
            SyncJobProgressService::FETCH_FPL_DATA,
            3,
            'Queued for syncing.'
        );

        $response = $this
            ->actingAs($admin)
            ->getJson(route('admin.data.status'));

        $response
            ->assertOk()
            ->assertJsonPath('summary.total_leagues', 1)
            ->assertJsonPath('summary.processing_leagues', 1)
            ->assertJsonPath('leagues.0.progress', 25)
            ->assertJsonFragment(['key' => SyncJobProgressService::FETCH_FPL_DATA]);
    }

    public function test_admin_can_refresh_specific_league_via_json_without_full_page_redirect(): void
    {
        Bus::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $leagueOwner = User::factory()->create();

        $league = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 5301,
            'name' => 'Refresh League',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $response = $this
            ->actingAs($admin)
            ->postJson(route('admin.data.refreshLeague', $league));

        $response
            ->assertOk()
            ->assertJsonPath('message', 'League refresh queued for Refresh League.');

        $this->assertDatabaseHas('leagues', [
            'id' => $league->id,
            'sync_status' => 'processing',
        ]);

        Bus::assertDispatched(FetchLeagueStandings::class, 1);
    }

    public function test_admin_can_delete_specific_league_via_json_endpoint(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $leagueOwner = User::factory()->create();

        $league = League::create([
            'user_id' => $leagueOwner->id,
            'league_id' => 5401,
            'name' => 'Delete League',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 777888,
            'player_name' => 'Delete Manager',
            'team_name' => 'Delete Team',
            'rank' => 1,
            'total_points' => 99,
        ]);

        $response = $this
            ->actingAs($admin)
            ->deleteJson(route('admin.data.destroyLeague', $league));

        $response
            ->assertOk()
            ->assertJsonPath('message', 'League Delete League deleted successfully.');

        $this->assertDatabaseMissing('leagues', [
            'id' => $league->id,
        ]);

        $this->assertDatabaseMissing('managers', [
            'id' => $manager->id,
        ]);
    }

    public function test_non_admin_cannot_access_admin_data_sync_json_endpoints(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $owner = User::factory()->create();
        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 5501,
            'name' => 'Forbidden League',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $this
            ->actingAs($user)
            ->getJson(route('admin.data.status'))
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->postJson(route('admin.data.syncAll'))
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->deleteJson(route('admin.data.destroyLeague', $league))
            ->assertForbidden();
    }
}
