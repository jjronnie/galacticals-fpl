<?php

namespace App\Http\Controllers;

use App\Models\FplPlayer;
use App\Models\FplTeam;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FplImageController extends Controller
{
    private const ALLOWED_DOMAIN = 'resources.premierleague.com';

    private const TEAM_CACHE_DAYS = 30;

    private const PLAYER_CACHE_DAYS = 7;

    public function team(int $teamId): Response
    {
        $team = FplTeam::find($teamId);

        if (! $team || ! $team->badgeUrl()) {
            return $this->fallbackImage('team');
        }

        return $this->serveCachedImage(
            $team->badgeUrl(),
            "fpl-cache/team/{$teamId}.png",
            self::TEAM_CACHE_DAYS,
            "img:team:{$teamId}"
        );
    }

    public function player(int $playerId): Response
    {
        $player = FplPlayer::find($playerId);

        if (! $player || ! $player->photoUrl()) {
            return $this->fallbackImage('player');
        }

        return $this->serveCachedImage(
            $player->photoUrl(),
            "fpl-cache/player/{$playerId}.png",
            self::PLAYER_CACHE_DAYS,
            "img:player:{$playerId}"
        );
    }

    private function serveCachedImage(string $url, string $storagePath, int $cacheDays, string $lockKey): Response
    {
        $disk = Storage::disk('public');

        if ($disk->exists($storagePath)) {
            return $this->streamFile($disk, $storagePath, $cacheDays);
        }

        if (! $this->isAllowedUrl($url)) {
            return $this->fallbackImage(str_contains($storagePath, 'team') ? 'team' : 'player');
        }

        return Cache::lock($lockKey, 15)->get(function () use ($url, $storagePath, $cacheDays, $disk) {
            if ($disk->exists($storagePath)) {
                return $this->streamFile($disk, $storagePath, $cacheDays);
            }

            try {
                $response = Http::timeout(10)
                    ->retry(2, 1000)
                    ->get($url);

                if (! $response->successful() || ! str_starts_with($response->header('Content-Type') ?? '', 'image/')) {
                    return $this->fallbackImage(str_contains($storagePath, 'team') ? 'team' : 'player');
                }

                $disk->put($storagePath, $response->body(), 'public');

                return $this->streamFile($disk, $storagePath, $cacheDays);
            } catch (\Throwable $e) {
                Log::warning('FPL image proxy fetch failed.', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);

                return $this->fallbackImage(str_contains($storagePath, 'team') ? 'team' : 'player');
            }
        }) ?? $this->fallbackImage(str_contains($storagePath, 'team') ? 'team' : 'player');
    }

    private function streamFile($disk, string $path, int $cacheDays): Response
    {
        $maxAge = $cacheDays * 86400;
        $content = $disk->get($path);

        return response($content, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => "public, max-age={$maxAge}",
            'Expires' => gmdate('D, d M Y H:i:s', time() + $maxAge).' GMT',
        ]);
    }

    private function fallbackImage(string $type): Response
    {
        $fallbackPath = "fpl-cache/fallback-{$type}.png";
        $disk = Storage::disk('public');

        if ($disk->exists($fallbackPath)) {
            return $this->streamFile($disk, $fallbackPath, 365);
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><rect width="40" height="40" fill="#374151" rx="4"/><text x="20" y="24" text-anchor="middle" fill="#9CA3AF" font-size="14" font-family="sans-serif">?</text></svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    private function isAllowedUrl(string $url): bool
    {
        $parsed = parse_url($url);

        return isset($parsed['host']) && $parsed['host'] === self::ALLOWED_DOMAIN;
    }
}
