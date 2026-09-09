<?php

return [
    'api_url' => env('PLATFORM_API_URL', 'http://localhost:8080/api/v1'),
    'api_key' => env('PLATFORM_API_KEY', 'absensi_live_placeholder'),
    'app_code' => env('APP_PLATFORM_CODE', 'absensi'),
    'cache_ttl' => env('PLATFORM_CACHE_TTL', 300),
];
