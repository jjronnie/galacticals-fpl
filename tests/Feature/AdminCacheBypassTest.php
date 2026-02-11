<?php

namespace Tests\Feature;

use App\Helpers\AdminCacheHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCacheBypassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/testing/admin-cache-probe', function () {
            $probeValue = AdminCacheHelper::remember('testing_admin_cache_probe', now()->addMinutes(10), function (): string {
                return (string) Str::uuid();
            });

            return response()->json([
                'driver' => app('cache')->getDefaultDriver(),
                'probe' => $probeValue,
            ]);
        });
    }

    public function test_admin_requests_bypass_persistent_cache_across_requests(): void
    {
        $this->useDatabaseCacheDriver();
        Cache::forget('testing_admin_cache_probe');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $firstResponse = $this
            ->actingAs($admin)
            ->getJson('/testing/admin-cache-probe');

        $secondResponse = $this
            ->actingAs($admin)
            ->getJson('/testing/admin-cache-probe');

        $firstResponse->assertOk()->assertJsonPath('driver', 'database');
        $secondResponse->assertOk()->assertJsonPath('driver', 'database');

        $this->assertNotSame(
            $firstResponse->json('probe'),
            $secondResponse->json('probe')
        );
    }

    public function test_non_admin_requests_keep_using_configured_persistent_cache_driver(): void
    {
        $this->useDatabaseCacheDriver();
        Cache::forget('testing_admin_cache_probe');

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $firstResponse = $this
            ->actingAs($user)
            ->getJson('/testing/admin-cache-probe');

        $secondResponse = $this
            ->actingAs($user)
            ->getJson('/testing/admin-cache-probe');

        $firstResponse->assertOk()->assertJsonPath('driver', 'database');
        $secondResponse->assertOk()->assertJsonPath('driver', 'database');

        $this->assertSame(
            $firstResponse->json('probe'),
            $secondResponse->json('probe')
        );
    }

    private function useDatabaseCacheDriver(): void
    {
        config([
            'cache.default' => 'database',
        ]);

        Cache::clearResolvedInstance('cache');
        app()->forgetInstance('cache');
    }
}
