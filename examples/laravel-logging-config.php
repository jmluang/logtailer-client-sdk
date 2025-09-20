<?php

/**
 * Laravel Logging Configuration Example
 *
 * Add this channel to your config/logging.php file
 */

return [
    'channels' => [
        // ... other channels ...

        'logtailer' => [
            'driver' => 'custom',
            'via' => function () {
                $handler = new \Jmluang\Logtailer\Monolog\LogtailHandler(
                    config('logtailer.token'),
                    \Monolog\Level::Debug,
                    true,
                    config('logtailer.endpoint', 'http://localhost:8080/logs'),
                    config('logtailer.buffer_limit', 1000),
                    true,
                    config('logtailer.connection_timeout_ms', 5000),
                    config('logtailer.timeout_ms', 5000),
                    config('logtailer.flush_interval_ms', 5000)
                );

                return new \Monolog\Logger('logtailer', [$handler]);
            },
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        // Stack channel example - sends to both file and logtailer
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'logtailer'],
            'ignore_exceptions' => false,
        ],
    ],
];