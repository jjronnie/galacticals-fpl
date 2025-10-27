<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Honeypot\ProtectAgainstSpam;
use App\Http\Middleware\BlockSuspendedUsers;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;



use Spatie\Permission\Exceptions\UnauthorizedException;



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

            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,

        ]);
    })


    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedException $e, $request) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not authorized to perform this action. Please Contact Admin for authorization.');
        });
    })->create();

