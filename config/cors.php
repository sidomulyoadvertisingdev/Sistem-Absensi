<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:5174',        // ⬅ TAMBAHKAN INI
        'http://127.0.0.1:5174',        // ⬅ DAN INI
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // BENAR → karena pakai Authorization: Bearer TOKEN
    'supports_credentials' => false,
];
