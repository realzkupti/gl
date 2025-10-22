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
        ]);
        // Ensure Livewire (and all web requests) also get the company connection applied
        $middleware->appendToGroup('web', [App\Http\Middleware\SetCompanyConnection::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
