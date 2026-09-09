<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Aplikasi mobile: Bearer token (Sanctum). Pastikan $request->user()
        // pada callback channel di routes/channels.php ikut memakai guard ini.
        if (Auth::guard('sanctum')->check()) {
            Auth::shouldUse('sanctum');

            return $next($request);
        }

        // Panel admin (browser): sesi/cookie.
        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web');

            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized.'], 401);
    }
}
