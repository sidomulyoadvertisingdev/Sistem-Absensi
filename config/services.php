<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Maps — dipakai admin untuk memilih titik lokasi absensi
    |--------------------------------------------------------------------------
    | Hanya untuk peta di panel admin (satelit + pencarian alamat).
    | Bila kosong, form otomatis fallback ke OpenStreetMap (Leaflet).
    | Wajib dibatasi di Google Cloud Console: HTTP referrer = domain aplikasi,
    | API = "Maps JavaScript API" + "Places API" saja.
    */
    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | ERP (ERPSIDOMULYO) — External API partner key
    |--------------------------------------------------------------------------
    | Dipakai fitur produksi: menarik daftar item produksi & menutup item
    | selesai. Auth: header X-Api-Key / X-Api-Secret (scope: production).
    */
    'erp' => [
        'base_url' => env('ERP_BASE_URL', 'https://dashboard.sidomulyoproject.com/api'),
        'api_key' => env('ERP_PARTNER_API_KEY'),
        'api_secret' => env('ERP_PARTNER_API_SECRET'),
        'timeout' => (int) env('ERP_API_TIMEOUT', 15),
        'production_jobs_enabled' => env('PRODUCTION_JOB_SYNC_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Deteksi "badan penuh" untuk absensi kios (MediaPipe Pose via Python)
    |--------------------------------------------------------------------------
    | Laravel shell-out ke scripts/pose/detect_fullbody.py.
    | Bila python/mediapipe tidak tersedia -> endpoint balas available:false
    | dan aplikasi otomatis jatuh ke foto manual.
    */
    'pose' => [
        'enabled' => filter_var(env('POSE_DETECTION_ENABLED', true), FILTER_VALIDATE_BOOL),
        'python_bin' => env('POSE_PYTHON_BIN', 'python3'),
        'script_path' => env('POSE_SCRIPT_PATH', base_path('scripts/pose/detect_fullbody.py')),
        'timeout' => (int) env('POSE_TIMEOUT', 15),
    ],

];
