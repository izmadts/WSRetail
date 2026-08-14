<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The Customer API (api/v1/customer/*) is called directly from the
    | storefront's browser bundle (see lib/api.ts in wsretail-storefront) -
    | only /connect is proxied server-side. Every other endpoint needs these
    | headers or the browser blocks the response before JS ever sees it.
    |
    | CORS_ALLOWED_ORIGINS is a comma-separated list in .env so each
    | environment (local dev, staging, production storefront domain) can
    | set its own without editing this file.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,http://127.0.0.1:3000'))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
