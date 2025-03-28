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

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // In production, replace '*' with specific domains that should access your API
    // For example: ['https://example.com', 'https://app.example.com']
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),

    // You can also use patterns for subdomains, etc.
    'allowed_origins_patterns' => explode(',', env('CORS_ALLOWED_ORIGIN_PATTERNS', '')),

    'allowed_headers' => [
        'X-Requested-With',
        'Content-Type',
        'Accept',
        'Authorization',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ],

    // Allow clients to access certain headers in the response
    'exposed_headers' => [
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma',
    ],

    // How long the results of a preflight request can be cached (in seconds)
    'max_age' => 86400, // 24 hours

    // Allow cookies to be included in cross-origin requests
    // Set to true only if you need to support authentication with cookies
    'supports_credentials' => false,

];
