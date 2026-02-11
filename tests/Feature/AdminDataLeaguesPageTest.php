<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDataLeaguesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_admin_leagues_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $owner = User::factory()->create([
            'name' => 'League Owner',
        ]);

        League::create([
            'user_id' => $owner->id,
            'league_id' => 551122,
            'name' => 'Admin League Page Test',
            'admin_name' => 'Owner',
            'current_gameweek' => 1,
            'season' => 2025,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.data.leagues'))
            ->assertOk()
            ->assertSee('Admin Leagues')
            ->assertSee('Admin League Page Test')
            ->assertSee('551122');
    }

    public function test_non_admin_cannot_view_admin_leagues_page(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->get(route('admin.data.leagues'))
            ->assertForbidden();
    }
}
