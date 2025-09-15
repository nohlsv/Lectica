<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustHosts(at: ['www.lectica.tech', 'lectica.tech', 'lectica-app-5y8wm.ondigitalocean.app']);
        $middleware->trustProxies(at: '*');
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->append(
            [
                \App\Http\Middleware\LogAccess::class,
            ]
        );

        // Add CORS middleware to API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Add CORS middleware to web routes as well
        $middleware->web(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\AssignDailyQuests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
