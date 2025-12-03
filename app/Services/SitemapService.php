<?php

namespace App\Services;

use App\Models\League;
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
       
            $sitemap->add(Url::create(route('table'))
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.7));
        

        // Dynamic league pages
        League::query()
            ->select('slug')
            ->chunk(100, function ($leagues) use ($sitemap) {
                foreach ($leagues as $league) {
                    $sitemap->add(Url::create(route('public.leagues.show', $league->slug))
                        ->setLastModificationDate(now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.9));
                }
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }


}