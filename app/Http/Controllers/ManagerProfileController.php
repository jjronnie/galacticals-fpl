<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use App\Services\ProfileStatsService;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ManagerProfileController extends Controller
{
    private const PROFILE_SECTIONS = [
        'overview' => 'Overview',
        'contributions' => 'Top Contributions',
        'chips' => 'Chip Usage',
        'captaincy' => 'Captain Performance',
        'transfers' => 'Transfer Efficiency',
        'value' => 'Squad Value Evolution',
        'history' => 'Gameweek History',
    ];

    public function __construct(
        private readonly ProfileStatsService $profileStatsService,
        private readonly SeoService $seoService
    ) {}

    public function show(int $entryId): View
    {
        return $this->renderProfile($entryId, 'overview');
    }

    public function section(int $entryId, string $section): View
    {
        return $this->renderProfile($entryId, $section);
    }

    private function renderProfile(int $entryId, string $section): View
    {
        if (! array_key_exists($section, self::PROFILE_SECTIONS)) {
            abort(404);
        }

        $managerRows = Manager::query()
            ->where('entry_id', $entryId)
            ->whereNull('suspended_at')
            ->get();

        if ($managerRows->isEmpty()) {
            abort(404);
        }

        $manager = $managerRows
            ->sortByDesc(fn (Manager $candidate): int => (
                (($candidate->user_id !== null) ? 1_000_000_000_000_000 : 0)
                + (($candidate->claimed_at?->timestamp ?? 0) * 1_000)
                + ($candidate->last_synced_at?->timestamp ?? 0)
            ))
            ->first()
            ->load(['favouriteTeam', 'league']);

        $isClaimed = $managerRows->contains(fn (Manager $candidate): bool => $candidate->user_id !== null);
        $isVerified = $managerRows->contains(fn (Manager $candidate): bool => $candidate->user_id !== null && $candidate->verified_at !== null);
        $stats = $isClaimed ? $this->profileStatsService->getProfileStats($manager, $section) : null;

        $leagueMemberships = Manager::query()
            ->where('entry_id', $entryId)
            ->whereHas('league')
            ->with('league:id,name,slug')
            ->get()
            ->pluck('league')
            ->filter()
            ->unique('id')
            ->values();

        $this->seoService->setPublicProfile($manager);

        return view('managers.show', [
            'manager' => $manager,
            'stats' => $stats,
            'isClaimed' => $isClaimed,
            'isVerified' => $isVerified,
            'leagueMemberships' => $leagueMemberships,
            'activeSection' => $section,
            'profileSections' => self::PROFILE_SECTIONS,
        ]);
    }

    public function short(string $code): RedirectResponse
    {
        $entryId = base_convert(trim($code), 36, 10);

        if ($entryId === '' || ! ctype_digit((string) $entryId)) {
            abort(404);
        }

        return redirect()->route('managers.show', ['entryId' => (int) $entryId]);
    }
}
