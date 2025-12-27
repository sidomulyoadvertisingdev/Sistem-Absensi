<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini dibuat untuk:
    | - React (Vite) di http://localhost:5173
    | - Mobile WebView / APK
    | - Auth menggunakan Bearer Token (Sanctum)
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    /*
     * Izinkan semua HTTP method
     */
    'allowed_methods' => ['*'],

    /*
     * Origin yang diizinkan
     * (Vite default = 5173)
     */
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    /*
     * Tidak pakai pattern dulu
     */
    'allowed_origins_patterns' => [],

    /*
     * Izinkan semua header
     */
    'allowed_headers' => ['*'],

    /*
     * Tidak expose header tambahan
     */
    'exposed_headers' => [],

    /*
     * Cache preflight
     */
    'max_age' => 0,

    /*
     * ❗ PENTING
     * false karena kita pakai Authorization: Bearer TOKEN
     * BUKAN cookie / session
     */
    'supports_credentials' => false,

];
