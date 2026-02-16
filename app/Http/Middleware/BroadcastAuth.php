<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('sanctum')->check() || Auth::check()) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized.'], 401);
    }
}
