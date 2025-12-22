<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * EXCLUDE SEMUA API DARI CSRF
     */
    protected $except = [
        'api/*',
        'sanctum/csrf-cookie',
    ];
}
