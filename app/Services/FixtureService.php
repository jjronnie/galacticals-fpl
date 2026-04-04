<?php

namespace App\Services;

use App\Models\FplFixture;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FixtureService
{
    public static function getCurrentEvent(): ?int
    {
        return Cache::remember('fpl.current_event', now()->addHours(6), function (): ?int {
            try {
                $response = Http::timeout(10)
                    ->retry(2, 1000, throw: false)
                    ->get(config('services.fpl.base_url', 'https://fantasy.premierleague.com/api').'/bootstrap-static/');

                if (! $response->successful()) {
                    return FplFixture::query()->whereNotNull('event')->max('event');
                }

                $data = $response->json();

                if (! is_array($data) || ! isset($data['events'])) {
                    return FplFixture::query()->whereNotNull('event')->max('event');
                }

                $currentEvent = collect($data['events'])
                    ->firstWhere('is_current', true);

                if ($currentEvent) {
                    return (int) $currentEvent['id'];
                }

                $nextEvent = collect($data['events'])
                    ->firstWhere('is_next', true);

                if ($nextEvent) {
                    return (int) $nextEvent['id'];
                }
            } catch (\Throwable) {
                //
            }

            return FplFixture::query()->whereNotNull('event')->max('event');
        });
    }

    public static function getAvailableEvents(): array
    {
        return FplFixture::query()
            ->whereNotNull('event')
            ->distinct()
            ->orderByDesc('event')
            ->pluck('event')
            ->all();
    }
}
