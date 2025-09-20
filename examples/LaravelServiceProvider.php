<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Monolog\Level;
use Jmluang\Logtailer\Monolog\LogtailHandler;

/**
 * Example Laravel Service Provider for Logtailer
 *
 * Copy this to app/Providers/LogtailerServiceProvider.php
 * Then register it in config/app.php providers array
 */
class LogtailerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the config file
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/logtailer.php',
            'logtailer'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__ . '/../../config/logtailer.php' => config_path('logtailer.php'),
        ], 'config');

        // Only add handler if enabled
        if (!config('logtailer.enabled', true)) {
            return;
        }

        // Get the token
        $token = config('logtailer.token');
        if (!$token) {
            return; // Skip if no token configured
        }

        // Add Logtailer handler to Laravel's logging
        $this->app['log']->extend('logtailer', function ($app, array $config) use ($token) {
            $handler = new LogtailHandler(
                $token,
                $this->getLevel($config['level'] ?? config('logtailer.level', 'debug')),
                $config['bubble'] ?? true,
                $config['endpoint'] ?? config('logtailer.endpoint'),
                $config['buffer_limit'] ?? config('logtailer.buffer_limit'),
                true,
                $config['connection_timeout_ms'] ?? config('logtailer.connection_timeout_ms'),
                $config['timeout_ms'] ?? config('logtailer.timeout_ms'),
                $config['flush_interval_ms'] ?? config('logtailer.flush_interval_ms')
            );

            return new Logger('logtailer', [$handler]);
        });
    }

    /**
     * Convert string level to Monolog Level
     */
    private function getLevel(string $level): Level
    {
        return match (strtolower($level)) {
            'debug' => Level::Debug,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            'error' => Level::Error,
            'critical' => Level::Critical,
            'alert' => Level::Alert,
            'emergency' => Level::Emergency,
            default => Level::Debug,
        };
    }
}