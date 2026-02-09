<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;

class FrontendController extends Controller
{
    protected $seoService;

    protected $statsService;

    public function __construct(SEOService $seoService)
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

    public function home()
    {
        $this->seoService->setHome();

        return view('pages.home');
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
