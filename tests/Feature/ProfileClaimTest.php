<?php

namespace Tests\Feature;

use App\Jobs\FetchManagerProfilesJob;
use App\Models\GameweekScore;
use App\Models\League;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProfileClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_requires_authentication(): void
    {
        $response = $this->get(route('profile.search'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_claim_manager_entry_across_matching_rows(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $claimer = User::factory()->create();

        $leagueA = League::create([
            'user_id' => $owner->id,
            'league_id' => 1001,
            'name' => 'League A',
            'admin_name' => 'Admin A',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $leagueB = League::create([
            'user_id' => $otherOwner->id,
            'league_id' => 1002,
            'name' => 'League B',
            'admin_name' => 'Admin B',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $managerOne = Manager::create([
            'league_id' => $leagueA->id,
            'entry_id' => 987654,
            'player_name' => 'John Doe',
            'team_name' => 'Team One',
            'rank' => 1,
            'total_points' => 100,
        ]);

        Manager::create([
            'league_id' => $leagueB->id,
            'entry_id' => 987654,
            'player_name' => 'John Doe',
            'team_name' => 'Team One',
            'rank' => 2,
            'total_points' => 95,
        ]);

        $response = $this
            ->actingAs($claimer)
            ->post(route('profile.claim', $managerOne));

        $response->assertRedirect(route('profile.index'));

        $this->assertDatabaseCount('managers', 2);
        $this->assertDatabaseHas('managers', ['entry_id' => 987654, 'user_id' => $claimer->id, 'league_id' => $leagueA->id]);
        $this->assertDatabaseHas('managers', ['entry_id' => 987654, 'user_id' => $claimer->id, 'league_id' => $leagueB->id]);

        Queue::assertPushed(FetchManagerProfilesJob::class);
    }

    public function test_claim_fails_when_entry_is_already_claimed_by_another_user(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $claimer = User::factory()->create();
        $existingClaimer = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 3001,
            'name' => 'Claimed League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 112233,
            'player_name' => 'Claimed Manager',
            'team_name' => 'Claimed Team',
            'rank' => 10,
            'total_points' => 40,
            'user_id' => $existingClaimer->id,
            'claimed_at' => now(),
        ]);

        $response = $this
            ->actingAs($claimer)
            ->from(route('profile.search', ['q' => $manager->entry_id]))
            ->post(route('profile.claim', $manager));

        $response
            ->assertRedirect(route('profile.search', ['q' => $manager->entry_id]))
            ->assertSessionHasErrors('claim');

        Queue::assertNothingPushed();
    }

    public function test_user_can_only_claim_one_profile_entry_at_a_time(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $owner = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 4001,
            'name' => 'Single Claim League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $firstManager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 200001,
            'player_name' => 'First Manager',
            'team_name' => 'First Team',
            'rank' => 1,
            'total_points' => 100,
            'user_id' => $user->id,
            'claimed_at' => now(),
        ]);

        $secondManager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 200002,
            'player_name' => 'Second Manager',
            'team_name' => 'Second Team',
            'rank' => 2,
            'total_points' => 90,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('profile.claim', $secondManager));

        $response
            ->assertRedirect(route('profile.index'))
            ->assertSessionHasErrors('claim');

        $this->assertDatabaseHas('managers', [
            'id' => $firstManager->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('managers', [
            'id' => $secondManager->id,
            'user_id' => null,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_search_redirects_when_user_already_has_claimed_profile(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 4101,
            'name' => 'Search Restriction League',
            'admin_name' => 'Admin',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        Manager::create([
            'league_id' => $league->id,
            'entry_id' => 333444,
            'player_name' => 'Claimed Manager',
            'team_name' => 'Claimed Team',
            'rank' => 1,
            'total_points' => 120,
            'user_id' => $user->id,
            'claimed_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('profile.search', ['q' => 'Claimed Team']));

        $response->assertRedirect(route('profile.index'));
    }

    public function test_live_search_endpoint_returns_results_with_overall_rank(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        $league = League::create([
            'user_id' => $owner->id,
            'league_id' => 4201,
            'name' => 'Live Search League',
            'admin_name' => 'Admin',
            'current_gameweek' => 2,
            'season' => 2025,
        ]);

        $manager = Manager::create([
            'league_id' => $league->id,
            'entry_id' => 909090,
            'player_name' => 'Mored Manager',
            'team_name' => 'Mored Team',
            'rank' => 1,
            'total_points' => 123,
        ]);

        GameweekScore::create([
            'manager_id' => $manager->id,
            'gameweek' => 2,
            'season_year' => 2025,
            'points' => 60,
            'overall_rank' => 456789,
            'total_points' => 123,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('profile.search.results', ['q' => 'mored']));

        $response
            ->assertOk()
            ->assertJsonPath('data.0.entry_id', 909090)
            ->assertJsonPath('data.0.overall_rank', 456789);
    }
}
