<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\User;
use App\Services\DashboardStatsService;
use Illuminate\View\View;

class FeaturedController extends Controller
{
    public function __construct(
        protected DashboardStatsService $dashboardStatsService
    ) {}

    public function latest(): View
    {
        $user = auth()->user();
        $league = $this->resolveUserLeague($user);

        $managerOfWeek = null;
        if ($league) {
            $managerOfWeek = $this->dashboardStatsService->getManagerOfTheWeek($league);
        }

        $playerOfWeekHistory = $this->dashboardStatsService->getPlayerOfWeekHistory(1);
        $playerOfWeek = ! empty($playerOfWeekHistory) ? $playerOfWeekHistory[0] : null;

        return view('pages.latest', compact('managerOfWeek', 'playerOfWeek'));
    }

    private function resolveUserLeague(?User $user): ?League
    {
        if ($user === null) {
            return null;
        }

        $ownedLeague = League::query()
            ->where('user_id', $user->id)
            ->first();

        if ($ownedLeague !== null) {
            return $ownedLeague;
        }

        return League::query()
            ->whereHas('managers', function ($query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc('last_synced_at')
            ->orderByDesc('updated_at')
            ->first();
    }
}
