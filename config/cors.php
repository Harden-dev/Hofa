<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'https://localhost:5173', // Ajout pour le HTTPS local
        'http://127.0.0.1:5173',
        'https://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [
        '#^https?://localhost(:\d+)?$#',
        '#^https?://127\.0\.0\.1(:\d+)?$#',
        '#^https://backend\.hofa-ci\.org$#', // Correction: HTTPS uniquement
        '#^https://www\.backend\.hofa-ci\.org$#', // Correction: HTTPS uniquement
        // Suppression des patterns HTTP pour le domaine de production
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400, // Cache preflight pendant 24h

    'supports_credentials' => true, // Correction: mettre à true si vous utilisez des cookies/auth

];
