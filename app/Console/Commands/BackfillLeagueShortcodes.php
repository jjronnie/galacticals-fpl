<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\League;
use Illuminate\Support\Str;

class BackfillLeagueShortcodes extends Command
{
    protected $signature = 'leagues:backfill-shortcodes';
    protected $description = 'Generate unique shortcodes for existing leagues without one';

    public function handle()
    {
        $this->info('Backfilling shortcodes...');

        League::whereNull('shortcode')->orWhere('shortcode', '')->chunk(100, function ($leagues) {
            foreach ($leagues as $league) {
                $league->shortcode = $this->generateUniqueShortcode();
                $league->save();
            }
        });

        $this->info('Done.');
    }

    private function generateUniqueShortcode($length = 5)
    {
        do {
            $code = Str::upper(Str::random($length));
        } while (League::where('shortcode', $code)->exists());

        return $code;
    }
}
