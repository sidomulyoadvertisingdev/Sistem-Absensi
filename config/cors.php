<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini digunakan untuk:
    | - Frontend React (Vite)
    | - WebView / APK
    | - Auth via Authorization: Bearer TOKEN (Sanctum)
    |
    | Backend:
    | - https://admin.sidomulyoproject.com
    |
    */

    /*
     * Path yang dikenakan CORS
     */
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    /*
     * Izinkan semua HTTP Method
     */
    'allowed_methods' => ['*'],

    /*
     * Origin yang diizinkan
     */
    'allowed_origins' => [

        // === LOCAL DEVELOPMENT ===
        'http://localhost:5173',
        'http://127.0.0.1:5173',

        // === PRODUCTION FRONTEND ===
        'https://sidomulyoproject.com',
        'https://www.sidomulyoproject.com',

        // === JIKA FRONTEND ADA SUBDOMAIN ===
        'https://app.sidomulyoproject.com',

        // === WEBVIEW / APK (AMAN) ===
        'capacitor://localhost',
        'ionic://localhost',
    ],

    /*
     * Tidak pakai wildcard pattern
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
     * Cache preflight (0 = disable cache)
     */
    'max_age' => 0,

    /*
     * ❗ PENTING
     * false karena auth pakai Bearer Token
     * BUKAN cookie / session
     */
    'supports_credentials' => false,

];
