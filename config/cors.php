<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],

    'allowed_methods' => ['*'],

    // ⬇️ TAMBAHKAN domain Vercel kamu di sini
    'allowed_origins' => [
        'https://futsalnow-fe-git-main-maman-darusman.vercel.app',
        // Jika nanti frontend sudah di custom domain, tambahkan juga di sini
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ⬇️ Ini PENTING karena kamu pakai login (session/token), harus true
    'supports_credentials' => true,
];
