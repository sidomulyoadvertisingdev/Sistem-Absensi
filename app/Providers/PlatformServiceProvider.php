<?php

namespace App\Providers;

use App\Services\Platform\FeatureService;
use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FeatureService::class, function ($app) {
            return new FeatureService();
        });
    }

    public function provides(): array
    {
        return [FeatureService::class];
    }

    public function boot(): void
    {
        // Register middleware alias
        $router = $this->app->make('router');
        $router->aliasMiddleware('platform.auth', \App\Http\Middleware\PlatformAuthorization::class);
    }
}
