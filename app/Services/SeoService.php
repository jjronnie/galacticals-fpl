<?php

namespace App\Services;

use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\TwitterCard;

class SeoService
{
    public function setDefault(): void
    {
        SEOMeta::setTitle('Advanced Fantasy Premier League Stats');
        SEOMeta::setDescription('Get deeper mini league statistics for FPL. Track best performers, worst points, Hall of Shame, Only Men Standing, and more fun stats beyond what FPL provides.');
        SEOMeta::setCanonical(url()->current());
        SEOMeta::addKeyword(['FPL', 'Fantasy Premier League', 'FPL Stats', 'Mini League', 'FPL Galaxy', 'FPL Analytics']);

        OpenGraph::setTitle('FPL Galaxy - Advanced Fantasy Premier League Stats');
        OpenGraph::setDescription('Get deeper mini league statistics for FPL. Track best performers, worst points, Hall of Shame, Only Men Standing, and more fun stats.');
        OpenGraph::setUrl(url()->current());
        OpenGraph::addProperty('type', 'website');
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setType('summary_large_image');
        TwitterCard::setTitle('FPL Galaxy - Advanced Fantasy Premier League Stats');
        TwitterCard::setDescription('Get deeper mini league statistics for FPL.');
        TwitterCard::setImage(asset('assets/img/logo.webp'));

        JsonLd::setTitle('FPL Galaxy');
        JsonLd::setDescription('Advanced Fantasy Premier League statistics and analytics');
        JsonLd::setType('WebApplication');
    }

    public function setHome(): void
    {

        SEOMeta::setTitle('Get more  Fantasy Premier League mini-league centered stats');
        SEOMeta::setDescription('Get deeper mini league statistics for FPL. Track best performers, worst points, Hall of Shame, Only Men Standing, and more fun stats beyond what FPL provides.');
        SEOMeta::setCanonical(route('home'));

        SEOMeta::addKeyword(['FPL', 'Fantasy Premier League', 'FPL Stats', 'Mini League', 'FPL Galaxy', 'FPL Analytics']);

        OpenGraph::setTitle('Advanced Fantasy Premier League Stats');
        OpenGraph::setDescription('Get deeper mini league statistics for FPL. Track best performers, worst points, Hall of Shame, Only Men Standing, and more fun stats.');
        OpenGraph::setUrl(route('home'));
        OpenGraph::addProperty('type', 'website');
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setType('summary_large_image');
        TwitterCard::setTitle('Advanced Fantasy Premier League Stats');
        TwitterCard::setDescription('Get deeper mini league statistics for FPL.');
        TwitterCard::setImage(asset('assets/img/logo.webp'));

        JsonLd::setTitle('FPL Galaxy');
        JsonLd::setDescription('Advanced Fantasy Premier League statistics and analytics');
        JsonLd::setType('WebApplication');

        JsonLd::addValue('applicationCategory', 'Sports Analytics');
        JsonLd::addValue('offers', [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'USD',
        ]);
    }

    public function setLeague($league)
    {
        $title = "{$league->name} - FPL Galaxy V2 League Analytics";
        $description = "View advanced gameweek tables, longest top streak, ownership trends, chip insights, and detailed mini-league analytics for {$league->name}.";

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(route('public.leagues.show', $league->slug));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('public.leagues.show', $league->slug));
        OpenGraph::addProperty('type', 'article');
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));

        JsonLd::setTitle($league->name);
        JsonLd::setDescription($description);
        JsonLd::setType('Dataset');
    }

    public function setProfileDashboard(): void
    {
        $title = 'My FPL Profile Dashboard - FPL Galaxy V2';
        $description = 'Track your points trajectory, captaincy impact, transfer efficiency, chip history, bench impact, and club bias analytics.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(url()->current());
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));
    }

    public function setClaimSearch(): void
    {
        $title = 'Search and Claim Your FPL Team - FPL Galaxy V2';
        $description = 'Search by team name, manager name, or entry ID and claim your FPL team profile for advanced analytics.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(url()->current());
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));
    }

    public function setPublicProfile($manager): void
    {
        $title = "{$manager->team_name} ({$manager->entry_id}) - FPL Manager Profile";
        $description = "Public FPL profile for {$manager->player_name}. View points trajectory, captaincy impact, transfer history, and chip analytics.";

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(url()->current());
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));

        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::setType('ProfilePage');
    }

    public function setHowToFind(): void
    {
        $title = 'How to Find Your FPL League ID - FPL Galaxy';
        $description = 'Step-by-step guide to finding your Fantasy Premier League mini league ID. Easy instructions to get started with FPL Galaxy advanced stats.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(route('find'));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('find'));
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));

        JsonLd::setTitle('How to Find FPL League ID');
        JsonLd::setType('HowTo');
    }

    public function setStandings(): void
    {
        $title = 'Gameweek History';
        $description = 'View and compare FPL mini league standings with advanced statistics and gameweek-by-gameweek analysis.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(url()->current());
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));

    }

    public function setLeagues(): void
    {
        $title = 'All Leagues - FPL Galaxy';
        $description = 'View all leagues already using FPL Galaxy and explore their public stats.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(url()->current());
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));

    }

    public function setPrivacyPolicy(): void
    {
        $title = 'Privacy Policy - FPL Galaxy';
        $description = 'Read how FPL Galaxy handles your data, cookies, and account information.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(route('privacy.policy'));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('privacy.policy'));
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));
    }

    public function setTermsAndConditions(): void
    {
        $title = 'Terms and Conditions - FPL Galaxy';
        $description = 'Read the terms and conditions for using FPL Galaxy.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(route('terms'));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('terms'));
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));
    }

    public function setMore(): void
    {
        $title = 'More Tools - FPL Galaxy';
        $description = 'Explore extra tools, guides, and resources available on FPL Galaxy.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(route('more'));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('more'));
        OpenGraph::addImage(asset('assets/img/logo.webp'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('assets/img/logo.webp'));
    }
}
