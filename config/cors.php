<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    // 🔥 PENTING: MOBILE TIDAK KIRIM ORIGIN
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ❗ Karena pakai Authorization Bearer, BUKAN cookie
    'supports_credentials' => false,
];
