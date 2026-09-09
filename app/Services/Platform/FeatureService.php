<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeatureService
{
    protected array $config;

    public function __construct()
    {
        $this->config = [
            'api_url' => config('platform.api_url', env('PLATFORM_API_URL')),
            'api_key' => config('platform.api_key', env('PLATFORM_API_KEY')),
            'app_code' => config('platform.app_code', env('APP_PLATFORM_CODE', 'absensi')),
            'cache_ttl' => config('platform.cache_ttl', env('PLATFORM_CACHE_TTL', 300)),
        ];
    }

    public function getEnabledFeatures(int $tenantId): array
    {
        $cacheKey = "platform_features:{$this->config['app_code']}:{$tenantId}";

        return Cache::store('redis')->remember($cacheKey, $this->config['cache_ttl'], function () use ($tenantId) {
            return $this->fetchFeaturesFromPlatform($tenantId);
        });
    }

    public function isEnabled(int $tenantId, string $featureCode): bool
    {
        $features = $this->getEnabledFeatures($tenantId);
        return $features[$featureCode]['enabled'] ?? false;
    }

    public function getConfig(int $tenantId, string $featureCode): array
    {
        $features = $this->getEnabledFeatures($tenantId);
        return $features[$featureCode]['config'] ?? [];
    }

    public function getMenus(int $tenantId): array
    {
        $cacheKey = "platform_menus:{$this->config['app_code']}:{$tenantId}";

        return Cache::store('redis')->remember($cacheKey, $this->config['cache_ttl'], function () use ($tenantId) {
            return $this->fetchMenusFromPlatform($tenantId);
        });
    }

    public function getPermissions(int $tenantId, string $roleCode): array
    {
        $cacheKey = "platform_permissions:{$this->config['app_code']}:{$tenantId}:{$roleCode}";

        return Cache::store('redis')->remember($cacheKey, $this->config['cache_ttl'], function () use ($tenantId, $roleCode) {
            return $this->fetchPermissionsFromPlatform($tenantId, $roleCode);
        });
    }

    public function isRouteAllowed(int $tenantId, string $roleCode, string $routeName): bool
    {
        $permissions = $this->getPermissions($tenantId, $roleCode);
        return !empty($permissions) && in_array($routeName, $permissions);
    }

    public function flushTenantCache(int $tenantId): void
    {
        Cache::store('redis')->forget("platform_features:{$this->config['app_code']}:{$tenantId}");
        Cache::store('redis')->forget("platform_menus:{$this->config['app_code']}:{$tenantId}");
        $this->flushAllTenantRoles($tenantId);
    }

    public function flushAllCache(): void
    {
        Cache::store('redis')->flush();
    }

    public function handleFeatureToggleEvent(array $eventData): void
    {
        $tenantId = $eventData['tenant_id'] ?? null;
        $featureCode = $eventData['feature_code'] ?? null;

        if ($tenantId && $featureCode) {
            $this->flushTenantCache($tenantId);
        }
    }

    protected function fetchFeaturesFromPlatform(int $tenantId): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->config['api_key'],
        ])->get("{$this->config['api_url']}/api/v1/features/status", [
            'tenant' => $tenantId,
            'app' => $this->config['app_code'],
        ]);

        if (! $response->successful()) {
            Log::warning('Failed to fetch features from platform', [
                'tenant_id' => $tenantId,
                'app' => $this->config['app_code'],
                'response' => $response->body(),
            ]);
            return [];
        }

        return $response->json('features', []);
    }

    protected function fetchMenusFromPlatform(int $tenantId): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->config['api_key'],
        ])->get("{$this->config['api_url']}/api/v1/tenants/{$tenantId}/menus");

        return $response->successful() ? ($response->json('menus') ?? []) : [];
    }

    protected function fetchPermissionsFromPlatform(int $tenantId, string $roleCode): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->config['api_key'],
        ])->get("{$this->config['api_url']}/api/v1/tenants/{$tenantId}/roles/{$roleCode}");

        return $response->successful() ? ($response->json('permissions') ?? []) : [];
    }

    protected function flushAllTenantRoles(int $tenantId): void
    {
        $roles = $this->getAllRolesFromPlatform($tenantId);
        foreach ($roles as $role) {
            Cache::store('redis')->forget(
                "platform_permissions:{$this->config['app_code']}:{$tenantId}:{$role}"
            );
        }
    }

    protected function getAllRolesFromPlatform(int $tenantId): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->config['api_key'],
        ])->get("{$this->config['api_url']}/api/v1/tenants/{$tenantId}/roles");

        return $response->successful() ? ($response->json('data') ?? []) : [];
    }
}
