<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://backend.hofa-ci.org',
        'https://www.backend.hofa-ci.org',
        'http://backend.hofa-ci.org',
        'http://www.backend.hofa-ci.org',
    ],

    'allowed_origins_patterns' => [
        '#^https?://localhost(:\d+)?$#',     // Accepte localhost avec n'importe quel port
        '#^https?://127\.0\.0\.1(:\d+)?$#', // Accepte 127.0.0.1 avec n'importe quel port
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
