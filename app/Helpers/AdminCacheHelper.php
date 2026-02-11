<?php

namespace App\Helpers;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminCacheHelper
{
    public static function remember(string $key, DateTimeInterface|DateInterval|int|null $ttl, Closure $callback): mixed
    {
        if (self::shouldBypassCache()) {
            return $callback();
        }

        return Cache::remember($key, $ttl, $callback);
    }

    private static function shouldBypassCache(): bool
    {
        $authenticatedUser = Auth::user();

        return $authenticatedUser !== null
            && method_exists($authenticatedUser, 'isAdmin')
            && $authenticatedUser->isAdmin();
    }
}
