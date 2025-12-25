<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    /*
    |----------------------------------------------------------------------
    | ROUTING
    |----------------------------------------------------------------------
    */
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    /*
    |----------------------------------------------------------------------
    | MIDDLEWARE
    |----------------------------------------------------------------------
    */
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |----------------------------------------------------------
        | ALIAS MIDDLEWARE (WAJIB UNTUK LARAVEL 11)
        |----------------------------------------------------------
        */
        $middleware->alias([
            'auth'     => \App\Http\Middleware\Authenticate::class,
            'guest'    => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        /*
        |----------------------------------------------------------
        | WEB MIDDLEWARE GROUP (WAJIB ADA SESSION)
        |----------------------------------------------------------
        */
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        /*
        |----------------------------------------------------------
        | API MIDDLEWARE (NEXT.JS / MOBILE)
        |----------------------------------------------------------
        | - TANPA CSRF
        | - TANPA SESSION
        | - AMAN & RINGAN
        */
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })

    /*
    |----------------------------------------------------------------------
    | EXCEPTIONS
    |----------------------------------------------------------------------
    */
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
