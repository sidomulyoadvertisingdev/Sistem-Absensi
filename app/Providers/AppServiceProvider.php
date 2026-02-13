<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 🔥 WAJIB agar pagination rapi di AdminLTE
        Paginator::useBootstrap();

        RateLimiter::for('chat-send', function (Request $request) {
            $identifier = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(30)->by($identifier);
        });
    }
}
