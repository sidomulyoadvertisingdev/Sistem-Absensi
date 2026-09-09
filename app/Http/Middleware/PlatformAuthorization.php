<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Platform\FeatureService;

class PlatformAuthorization
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $routeName = $request->route()?->getName();

        if ($user && $routeName) {
            $tenantId = $user->tenant_id ?? $user->outlet_id ?? null;
            $roleCode = $user->role ?? $user->getRoleNames()->first();

            if ($tenantId && $roleCode) {
                $featureService = app(FeatureService::class);
                if (! $featureService->isRouteAllowed($tenantId, $roleCode, $routeName)) {
                    abort(403, 'Anda tidak memiliki akses ke rute ini.');
                }
            }
        }

        return $next($request);
    }
}
