<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Honeypot\ProtectAgainstSpam;
use App\Http\Middleware\BlockSuspendedUsers;







return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ProtectAgainstSpam::class,
            BlockSuspendedUsers::class,

        ]);

        $middleware->alias([

            'admin' => \App\Http\Middleware\AdminRole::class,
        ]);
    })


->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
        return redirect()->route('dashboard')
            ->with('error', 'You are not authorized to perform this action. Please contact admin for help.');
    });
})
->create();


