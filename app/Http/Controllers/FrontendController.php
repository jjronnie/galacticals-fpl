<?php

namespace App\Http\Controllers;

use App\Models\FplFixture;
use App\Models\League;
use App\Services\FixtureService;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontendController extends Controller
{
    protected $seoService;

    protected $statsService;

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    public function index()
    {
        $leagues = League::all(['name', 'slug']);

        $total = $leagues->count();

        $this->seoService->setLeagues();

        return view('leagues.index', compact('leagues', 'total'));

    }

    public function home(Request $request): View
    {
        $this->seoService->setHome();

        $event = $request->integer('event');

        if ($event > 0) {
            $currentEvent = $event;
        } else {
            $currentEvent = FixtureService::getCurrentEvent();
        }

        if ($currentEvent) {
            $fixtures = FplFixture::query()
                ->where('event', $currentEvent)
                ->with(['homeTeam', 'awayTeam'])
                ->orderBy('kickoff_time')
                ->get();
        } else {
            $fixtures = collect();
        }

        $groupedByDate = $fixtures->groupBy(function ($fixture) {
            return $fixture->kickoff_time
                ? $fixture->kickoff_time->format('l, j F Y')
                : 'TBC';
        });

        $maxEvent = FplFixture::query()->whereNotNull('event')->max('event');
        $minEvent = FplFixture::query()->whereNotNull('event')->min('event');

        $prevEvent = $currentEvent > $minEvent ? $currentEvent - 1 : null;
        $nextEvent = $currentEvent < $maxEvent ? $currentEvent + 1 : null;

        return view('pages.home', compact(
            'groupedByDate',
            'currentEvent',
            'prevEvent',
            'nextEvent',
            'maxEvent',
            'minEvent',
        ));
    }

    public function findLeagueID()
    {
        $this->seoService->setHowToFind();

        return view('pages.find');
    }

    public function policy()
    {
        $this->seoService->setPrivacyPolicy();

        return view('pages.privacy_policy');
    }

    public function terms()
    {
        $this->seoService->setTermsAndConditions();

        return view('pages.terms');
    }

    public function more()
    {
        $this->seoService->setMore();

        return view('pages.more');
    }

    public function shortCode($code): RedirectResponse
    {
        $league = League::where('shortcode', $code)->firstOrFail();

        return redirect()->route('public.leagues.show', [
            'slug' => $league->slug,
        ]);
    }

    public function updateFixtures(Request $request): JsonResponse
    {
        // Only allow AJAX requests
        if (! $request->ajax() && ! $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $event = $request->integer('event');
        if (! $event) {
            $event = app(FixtureService::class)->getCurrentEvent();
        }

        if (! $event) {
            return response()->json(['success' => false, 'message' => 'No event specified']);
        }

        $fixtures = FplFixture::query()
            ->where('event', $event)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('kickoff_time')
            ->get();

        $groupedByDate = $fixtures->groupBy(function ($fixture) {
            return $fixture->kickoff_time
                ? $fixture->kickoff_time->format('l, j F Y')
                : 'TBC';
        });

        $maxEvent = FplFixture::query()->whereNotNull('event')->max('event');
        $minEvent = FplFixture::query()->whereNotNull('event')->min('event');

        $prevEvent = $event > $minEvent ? $event - 1 : null;
        $nextEvent = $event < $maxEvent ? $event + 1 : null;

        // Render just the fixture HTML portion
        $html = view('partials.fixtures-content', compact(
            'groupedByDate',
            'event',
            'prevEvent',
            'nextEvent',
            'maxEvent',
            'minEvent'
        ))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }
}
