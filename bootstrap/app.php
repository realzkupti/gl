<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Route middleware aliases
        $middleware->alias([
            'company.connection' => App\Http\Middleware\SetCompanyConnection::class,
            'require.company' => App\Http\Middleware\RequireCompanySelection::class,
            'menu' => App\Http\Middleware\MenuPermission::class,
        ]);
        // Log user activities (append to web group)
        $middleware->appendToGroup('web', App\Http\Middleware\ActivityLogger::class);
        // Do NOT apply company connection globally.
        // We keep login/permission on pgsql and opt-in per-route where needed.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
