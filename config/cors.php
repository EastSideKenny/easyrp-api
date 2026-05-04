<?php

$allowedOrigins = array_values(array_filter(array_unique([
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    env('FRONTEND_URL'),
])));

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in the browser. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
     * Do not combine allowed_origins * with supports_credentials true — browsers block it.
     * This app authenticates via Bearer token in JSON, not cross-origin cookies.
     */
    'allowed_origins' => $allowedOrigins !== [] ? $allowedOrigins : ['*'],

    'allowed_origins_patterns' => [
        '#^https?://[a-z0-9-]+\\.localhost(:\\d+)?$#i',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
