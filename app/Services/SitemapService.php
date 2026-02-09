<?php

namespace App\Services;

use App\Models\League;
use App\Models\LeagueGameweekStanding;
use App\Models\Manager;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public static function update()
    {
        $sitemap = Sitemap::create();

        // Static pages
        $sitemap->add(Url::create(route('home'))
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1.0));

        $sitemap->add(Url::create(route('public.leagues.list'))
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(0.9));

        $sitemap->add(Url::create(route('more'))
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.6));

        $sitemap->add(Url::create(route('find'))
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.8));

        $sitemap->add(Url::create(route('privacy.policy'))
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.3));

        $sitemap->add(Url::create(route('terms'))
            ->setLastModificationDate(now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            ->setPriority(0.3));

        // Dynamic league pages
        League::query()
            ->select('id', 'slug', 'updated_at')
            ->chunk(100, function ($leagues) use ($sitemap) {
                $gameweeksByLeague = LeagueGameweekStanding::query()
                    ->whereIn('league_id', $leagues->pluck('id')->all())
                    ->select('league_id', 'gameweek')
                    ->distinct()
                    ->orderBy('gameweek')
                    ->get()
                    ->groupBy('league_id');

                foreach ($leagues as $league) {
                    $sitemap->add(Url::create(route('public.leagues.show', $league->slug))
                        ->setLastModificationDate($league->updated_at ?? now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.9));

                    foreach (($gameweeksByLeague[$league->id] ?? collect()) as $record) {
                        $sitemap->add(Url::create(route('public.leagues.gameweek.show', [
                            'slug' => $league->slug,
                            'gameweek' => (int) $record->gameweek,
                        ]))
                            ->setLastModificationDate($league->updated_at ?? now())
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                            ->setPriority(0.8));
                    }
                }
            });

        Manager::query()
            ->whereNull('suspended_at')
            ->select('entry_id')
            ->distinct()
            ->chunk(100, function ($managers) use ($sitemap) {
                foreach ($managers as $manager) {
                    $sitemap->add(Url::create(route('managers.show', $manager->entry_id))
                        ->setLastModificationDate(now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.8));
                }
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }
}
