<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL of the BrandGEO API, without a trailing slash. Point it at a
    | staging or local instance (e.g. https://brandgeo.test/api/v1) when
    | developing against a non-production environment.
    |
    */

    'base_url' => env('BRANDGEO_BASE_URL', 'https://brandgeo.co/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your BrandGEO API key, generated at Settings → API. Keys look like
    | "{id}|{random}" — send them verbatim. Use BrandGeo::withApiKey() to
    | switch keys at runtime (e.g. agency apps managing multiple accounts).
    |
    */

    'api_key' => env('BRANDGEO_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Request timeout in seconds.
    |
    */

    'timeout' => env('BRANDGEO_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | TLS Verification
    |--------------------------------------------------------------------------
    |
    | Disable only for local development against self-signed certificates
    | (e.g. a Laravel Herd instance). Keep enabled in production.
    |
    */

    'verify' => env('BRANDGEO_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | Retries
    |--------------------------------------------------------------------------
    |
    | Automatic retries for failed connections. Disabled by default; "sleep"
    | is the delay between attempts in milliseconds.
    |
    */

    'retry' => [
        'times' => env('BRANDGEO_RETRY_TIMES', 0),
        'sleep' => env('BRANDGEO_RETRY_SLEEP', 200),
    ],

];
