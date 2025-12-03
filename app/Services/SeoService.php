<?php

namespace App\Services;

use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;

class SeoService
{
    public function setDefault(): void
    {
        SEOMeta::setTitle('FPL Galaxy - Advanced Fantasy Premier League Stats');
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
        $this->setDefault();
        
        JsonLd::addValue('applicationCategory', 'Sports Analytics');
        JsonLd::addValue('offers', [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'USD'
        ]);
    }

    public function setLeague($league)
    {
        $title = "{$league->name} - FPL Galaxy League Stats";
        $description = "View detailed statistics for {$league->name}. Track gameweek performance, hall of shame, only men standing, biggest point swings, and more fun FPL stats.";

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(route('public.leagues.show', $league->slug));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('public.leagues.show', $league->slug));
        OpenGraph::addProperty('type', 'article');

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);

        JsonLd::setTitle($league->name);
        JsonLd::setDescription($description);
        JsonLd::setType('Dataset');
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

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);

        JsonLd::setTitle('How to Find FPL League ID');
        JsonLd::setType('HowTo');
    }

    public function setStandings(): void
    {
        $title = 'League Standings - FPL Galaxy';
        $description = 'View and compare FPL mini league standings with advanced statistics and gameweek-by-gameweek analysis.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(url()->current());

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
    }

        public function setLeagues(): void
    {
        $title = 'Al Leagues- FPL Galaxy';
        $description = 'View all leagues that are already using FPL Galaxy and see thier stats.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(url()->current());

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(url()->current());

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
    }
}