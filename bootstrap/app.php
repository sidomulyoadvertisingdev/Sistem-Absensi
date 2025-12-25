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
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
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
        | ALIAS (CUSTOM MIDDLEWARE)
        |--------------------------------------------------------------------------
        */
        $middleware->alias([
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | WEB MIDDLEWARE (ADMIN PANEL + BLADE + SESSION)
        |--------------------------------------------------------------------------
        | ⚠️ INI WAJIB ADA
        | Digunakan oleh:
        | - auth()->user()
        | - session
        | - popup update
        | - form admin
        */
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | API MIDDLEWARE (NEXT.JS / MOBILE)
        |--------------------------------------------------------------------------
        | CSRF NONAKTIF (AMAN UNTUK API)
        | Session tetap aktif jika dibutuhkan
        */
        $middleware->group('api', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | NONAKTIFKAN CSRF KHUSUS API (BUKAN WEB)
        |--------------------------------------------------------------------------
        */
        $middleware->removeFromGroup(
            'api',
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class
        );
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
