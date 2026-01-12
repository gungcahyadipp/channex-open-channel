<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Channex Open Channel API Key
    |--------------------------------------------------------------------------
    |
    | API key untuk mengirim request ke Channex (push booking, request sync).
    | Ini adalah API key yang diberikan oleh Channex untuk channel Anda.
    | Selama development, gunakan 'open_channel_api_key'.
    |
    */
    'api_key' => env('CHANNEX_API_KEY', 'open_channel_api_key'),

    /*
    |--------------------------------------------------------------------------
    | Inbound API Key
    |--------------------------------------------------------------------------
    |
    | API key untuk memvalidasi request yang masuk dari Channex ke endpoint
    | Anda (test_connection, mapping_details, changes). Channex akan mengirim
    | header 'api-key' dengan value ini.
    |
    */
    'inbound_api_key' => env('CHANNEX_INBOUND_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Provider Code
    |--------------------------------------------------------------------------
    |
    | Unique provider code yang diberikan oleh Channex.
    | Selama development, gunakan 'OpenChannel'.
    |
    */
    'provider_code' => env('CHANNEX_PROVIDER_CODE', 'OpenChannel'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Environment Channex: "staging" atau "production"
    |
    | - staging: staging.channex.io / secure-staging.channex.io
    | - production: app.channex.io / secure.channex.io
    |
    */
    'environment' => env('CHANNEX_ENVIRONMENT', 'staging'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints (Opsional Override)
    |--------------------------------------------------------------------------
    |
    | Biarkan null untuk menggunakan default berdasarkan environment.
    | Atau set manual jika Channex mengubah URL endpoint.
    |
    */
    'endpoints' => [
        // Base API endpoint (untuk request_full_sync)
        'api' => env('CHANNEX_API_ENDPOINT', null),

        // Secure API endpoint (untuk push booking dengan credit card)
        'secure' => env('CHANNEX_SECURE_ENDPOINT', null),
    ],
];
