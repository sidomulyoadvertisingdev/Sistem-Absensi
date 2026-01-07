<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | ROUTING
    |--------------------------------------------------------------------------
    */
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',

        api: [
            __DIR__ . '/../routes/api.php',
            __DIR__ . '/../routes/api.sidomulyo.php',
        ],

        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | MIDDLEWARE
    |--------------------------------------------------------------------------
    */
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | GLOBAL MIDDLEWARE (CORS)
        |--------------------------------------------------------------------------
        */
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | ALIAS MIDDLEWARE
        |--------------------------------------------------------------------------
        */
        $middleware->alias([
            'auth'     => \App\Http\Middleware\Authenticate::class,
            'guest'    => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'is_admin' => \App\Http\Middleware\IsAdmin::class,

            /*
            |--------------------------------------------------------------------------
            | ❗ JANGAN override auth:sanctum ❗
            | Laravel akan otomatis pakai Sanctum Guard
            |--------------------------------------------------------------------------
            */
            // ❌ JANGAN ADA auth:sanctum DI SINI
        ]);

        /*
        |--------------------------------------------------------------------------
        | WEB GROUP
        |--------------------------------------------------------------------------
        */
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | API GROUP (STATELESS)
        |--------------------------------------------------------------------------
        */
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })

    /*
    |--------------------------------------------------------------------------
    | EXCEPTIONS
    |--------------------------------------------------------------------------
    */
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
