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
        // Origines de production spécifiques
        'https://hofa-ci.org',
        'https://www.hofa-ci.org',
        'http://localhost:5173',
    ],

    'allowed_origins_patterns' => [
        // Patterns pour le développement local
        '#^https?://localhost(:\d+)?$#',
        '#^https?://127\.0\.0\.1(:\d+)?$#',

        // Patterns pour la production (si vous avez des sous-domaines)
        '#^https://([a-z0-9-]+\.)?hofa-ci\.org$#',

        // Pattern pour le backend (si nécessaire)
        '#^https://backend\.hofa-ci\.org$#',
        '#^https://www\.backend\.hofa-ci\.org$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        // Ajoutez ici les headers que votre frontend doit pouvoir lire
        // Par exemple : 'X-Total-Count', 'X-Page-Count'
    ],

    'max_age' => 86400, // 24 heures

    'supports_credentials' => true,

];
