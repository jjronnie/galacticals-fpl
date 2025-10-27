<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class BlockSuspendedUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in and their status is not active
        if (Auth::check() && Auth::user()->status !== 'active') {
            Auth::logout();

            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Oops!! Your account is not active. Please contact the administrator.'
                ]);
        }

        return $next($request);
    }
}
