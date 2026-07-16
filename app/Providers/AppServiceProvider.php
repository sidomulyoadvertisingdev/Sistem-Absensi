<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;

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

        Storage::extend('google', function ($app, $config) {
            $client = new \Google\Client();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->refreshToken($config['refreshToken']);
            $service = new \Google\Service\Drive($client);

            $adapter = new GoogleDriveAdapter($service, $config['folderId'] ?? null);

            return new Filesystem($adapter);
        });

        RateLimiter::for('chat-send', function (Request $request) {
            $identifier = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(30)->by($identifier);
        });
    }
}
