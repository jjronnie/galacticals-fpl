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
}
