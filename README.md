# Jmluang Logtailer Client SDK

A PHP SDK for integrating with Logtailer log aggregation service, with full Monolog 3.x support.

## Installation

```bash
composer require jmluang/logtailer-client-sdk
```

## Requirements

- PHP 8.1 or higher
- ext-curl
- ext-json
- monolog/monolog ^3.0

## Laravel Integration

### Step 1: Install the package

```bash
composer require jmluang/logtailer-client-sdk
```

### Step 2: Add configuration to .env

```env
LOGTAILER_TOKEN=your-source-token-here
LOGTAILER_ENDPOINT=http://localhost:8080/logs
LOGTAILER_ENABLED=true
```

### Step 3: Configure Laravel Logging

Add this channel to your `config/logging.php`:

```php
'channels' => [
    'logtailer' => [
        'driver' => 'custom',
        'via' => function () {
            $handler = new \Jmluang\Logtailer\Monolog\LogtailHandler(
                env('LOGTAILER_TOKEN'),
                \Monolog\Level::Debug,
                true,
                env('LOGTAILER_ENDPOINT', 'http://localhost:8080/logs')
            );
            return new \Monolog\Logger('logtailer', [$handler]);
        },
    ],

    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'logtailer'],
    ],
],
```

### Step 4: Use in your application

```php
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Payment failed', ['order_id' => $orderId]);
```

## Standard PHP Usage

### Basic Usage with LogtailHandler

```php
use Monolog\Logger;
use Monolog\Level;
use Jmluang\Logtailer\Monolog\LogtailHandler;

$logger = new Logger('my-app');

$handler = new LogtailHandler(
    'your-source-token',
    Level::Debug,
    true,
    'http://localhost:8080/logs'
);

$logger->pushHandler($handler);

// Use logger as normal
$logger->info('Application started');
$logger->error('An error occurred', ['error_code' => 500]);
```

### Using the Builder Pattern

```php
use Jmluang\Logtailer\Monolog\LogtailHandlerBuilder;

$handler = LogtailHandlerBuilder::withSourceToken('your-token')
    ->withEndpoint('http://localhost:8080/logs')
    ->withLevel(Level::Info)
    ->withBufferLimit(500)
    ->withFlushIntervalMilliseconds(3000)
    ->build();

$logger->pushHandler($handler);
```

## Testing

### Install Dependencies

```bash
composer install
```

### Run Tests

```bash
# Run all tests with documentation
./vendor/bin/phpunit --testdox

# Run tests with coverage report
./vendor/bin/phpunit --coverage-text

# Run specific test file
./vendor/bin/phpunit tests/Monolog/LogtailHandlerTest.php

# Run integration tests (requires running Logtailer service)
LOGTAILER_INTEGRATION_TEST=1 ./vendor/bin/phpunit --group integration
```

### Test Structure

```
tests/
├── Monolog/
│   ├── LogtailClientTest.php       # HTTP client tests
│   ├── LogtailFormatterTest.php    # JSON formatter tests
│   ├── LogtailHandlerTest.php      # Buffer handler tests
│   ├── LogtailHandlerBuilderTest.php # Builder pattern tests
│   └── SynchronousLogtailHandlerTest.php # Sync handler tests
```

## Configuration Options

### LogtailHandler

- `sourceToken` (string): Authentication token for Logtailer
- `level` (Level): Minimum log level (default: Debug)
- `bubble` (bool): Whether to bubble messages (default: true)
- `endpoint` (string): Logtailer endpoint URL
- `bufferLimit` (int): Max buffered entries (default: 1000)
- `flushOnOverflow` (bool): Flush when buffer is full (default: true)
- `connectionTimeoutMs` (int): Connection timeout in ms (default: 5000)
- `timeoutMs` (int): Request timeout in ms (default: 5000)
- `flushIntervalMs` (int|null): Auto-flush interval in ms (default: 5000)

## Examples

### Logging with Context

```php
$logger->info('User action', [
    'user_id' => 123,
    'action' => 'login',
    'ip' => '192.168.1.1',
    'metadata' => [
        'browser' => 'Chrome',
        'os' => 'macOS'
    ]
]);
```

### Exception Logging

```php
try {
    // Some operation
} catch (\Exception $e) {
    $logger->error('Operation failed', [
        'exception' => $e
    ]);
}
```

### Multiple Channels

```php
$apiLogger = new Logger('api');
$apiLogger->pushHandler($handler);

$dbLogger = new Logger('database');
$dbLogger->pushHandler($handler);

$apiLogger->info('API request received');
$dbLogger->info('Database query executed');
```

## License

MIT

## Support

For issues and feature requests, please open an issue on GitHub.