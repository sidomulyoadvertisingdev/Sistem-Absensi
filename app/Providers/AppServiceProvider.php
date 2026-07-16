<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\GoogleDriveAdapter as AppGoogleDriveAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;

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

            $folderId = $config['folderId'] ?? null;

            // toleransi: jika folderId berupa URL Drive, ekstrak ID-nya
            if ($folderId && str_contains($folderId, 'drive.google.com')) {
                preg_match('/[-\w]{25,}/', $folderId, $matches);
                $folderId = $matches[0] ?? null;
            }

            return new FilesystemAdapter(
                new Filesystem(new AppGoogleDriveAdapter($service, $folderId))
            );
        });

        RateLimiter::for('chat-send', function (Request $request) {
            $identifier = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(30)->by($identifier);
        });
    }
}
