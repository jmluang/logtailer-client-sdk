<?php

/**
 * Laravel Configuration Example
 *
 * Place this in config/logtailer.php
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Logtailer Source Token
    |--------------------------------------------------------------------------
    |
    | This is your authentication token for the Logtailer service.
    | You should set this in your .env file as LOGTAILER_TOKEN
    |
    */
    'token' => env('LOGTAILER_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Logtailer Endpoint
    |--------------------------------------------------------------------------
    |
    | The endpoint URL where logs will be sent.
    | Default is local development server.
    |
    */
    'endpoint' => env('LOGTAILER_ENDPOINT', 'http://localhost:8080/logs'),

    /*
    |--------------------------------------------------------------------------
    | Connection Settings
    |--------------------------------------------------------------------------
    |
    | Timeout settings for the HTTP connection
    |
    */
    'connection_timeout_ms' => env('LOGTAILER_CONNECTION_TIMEOUT', 5000),
    'timeout_ms' => env('LOGTAILER_TIMEOUT', 5000),

    /*
    |--------------------------------------------------------------------------
    | Buffer Settings
    |--------------------------------------------------------------------------
    |
    | Configure how logs are buffered before sending
    |
    */
    'buffer_limit' => env('LOGTAILER_BUFFER_LIMIT', 1000),
    'flush_interval_ms' => env('LOGTAILER_FLUSH_INTERVAL', 5000),

    /*
    |--------------------------------------------------------------------------
    | Logging Level
    |--------------------------------------------------------------------------
    |
    | Minimum level of logs to send to Logtailer
    | Options: debug, info, notice, warning, error, critical, alert, emergency
    |
    */
    'level' => env('LOGTAILER_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable
    |--------------------------------------------------------------------------
    |
    | You can disable Logtailer logging entirely
    |
    */
    'enabled' => env('LOGTAILER_ENABLED', true),
];