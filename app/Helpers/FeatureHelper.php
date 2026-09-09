<?php

namespace App\Helpers;

use App\Services\Platform\FeatureService;
use Illuminate\Support\Facades\Log;

class FeatureHelper
{
    public static function isEnabled(int $tenantId, string $featureCode): bool
    {
        try {
            $featureService = app(FeatureService::class);
            return $featureService->isEnabled($tenantId, $featureCode);
        } catch (\Exception $e) {
            Log::warning('Feature check failed', [
                'tenant_id' => $tenantId,
                'feature' => $featureCode,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public static function getConfig(int $tenantId, string $featureCode): array
    {
        try {
            $featureService = app(FeatureService::class);
            return $featureService->getConfig($tenantId, $featureCode);
        } catch (\Exception $e) {
            Log::warning('Feature config fetch failed', [
                'tenant_id' => $tenantId,
                'feature' => $featureCode,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public static function getMenus(int $tenantId): array
    {
        try {
            $featureService = app(FeatureService::class);
            return $featureService->getMenus($tenantId);
        } catch (\Exception $e) {
            Log::warning('Menu fetch failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public static function canRoleAccess(int $tenantId, string $roleCode, string $featureCode): bool
    {
        try {
            $featureService = app(FeatureService::class);
            $permissions = $featureService->getPermissions($tenantId, $roleCode);
            return !empty($permissions) && in_array($featureCode, $permissions);
        } catch (\Exception $e) {
            return false;
        }
    }
}
