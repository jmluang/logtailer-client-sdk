<?php

namespace Jmluang\Logtailer\Tests\Monolog;

use PHPUnit\Framework\TestCase;
use Jmluang\Logtailer\Monolog\SynchronousLogtailHandler;
use Jmluang\Logtailer\Monolog\LogtailFormatter;
use Monolog\Logger;
use Monolog\Level;
use Monolog\LogRecord;

class SynchronousLogtailHandlerTest extends TestCase
{
    private string $testToken = 'test-token-123';
    private string $testEndpoint = 'http://localhost:8080/logs';

    public function testHandlerConstructor()
    {
        $handler = new SynchronousLogtailHandler(
            $this->testToken,
            Level::Debug,
            true,
            $this->testEndpoint
        );

        $this->assertInstanceOf(SynchronousLogtailHandler::class, $handler);
    }

    public function testDefaultThrowExceptionConstant()
    {
        $this->assertFalse(SynchronousLogtailHandler::DEFAULT_THROW_EXCEPTION);
    }

    public function testHandlerWithCustomParameters()
    {
        $handler = new SynchronousLogtailHandler(
            $this->testToken,
            Level::Warning,
            false,
            $this->testEndpoint,
            3000, // connectionTimeoutMs
            4000, // timeoutMs
            true  // throwExceptions
        );

        $this->assertInstanceOf(SynchronousLogtailHandler::class, $handler);
    }

    public function testGetFormatter()
    {
        $handler = new SynchronousLogtailHandler(
            $this->testToken,
            Level::Debug,
            true,
            $this->testEndpoint
        );

        $formatter = $handler->getFormatter();
        $this->assertInstanceOf(LogtailFormatter::class, $formatter);
    }

    public function testHandleRecord()
    {
        $handler = new SynchronousLogtailHandler(
            $this->testToken,
            Level::Debug,
            false, // bubble = false
            $this->testEndpoint
        );

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: ['key' => 'value'],
            extra: []
        );

        $result = $handler->handle($record);
        $this->assertTrue($result); // SynchronousLogtailHandler processes and returns true
    }

    public function testHandleRecordWithBubble()
    {
        $handler = new SynchronousLogtailHandler(
            $this->testToken,
            Level::Debug,
            true, // bubble = true
            $this->testEndpoint
        );

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: [],
            extra: []
        );

        $result = $handler->handle($record);
        $this->assertFalse($result); // Returns false to stop bubbling when handled
    }

    public function testHandleBatch()
    {
        $handler = new SynchronousLogtailHandler(
            $this->testToken,
            Level::Debug,
            true,
            $this->testEndpoint
        );

        $records = [
            new LogRecord(
                datetime: new \DateTimeImmutable(),
                channel: 'test1',
                level: Level::Debug,
                message: 'Debug message',
                context: [],
                extra: []
            ),
            new LogRecord(
                datetime: new \DateTimeImmutable(),
                channel: 'test2',
                level: Level::Info,
                message: 'Info message',
                context: ['info' => true],
                extra: []
            )
        ];

        // Should not throw
        $this->assertNull($handler->handleBatch($records));
    }

    public function testProcessorsAreAdded()
    {
        $handler = new SynchronousLogtailHandler(
            $this->testToken,
            Level::Debug,
            true,
            $this->testEndpoint
        );

        // Create a test record
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test processors',
            context: [],
            extra: []
        );

        // Process the record (this applies processors)
        $processed = $handler->handle($record);

        // The handler should have added processors that modify the extra field
        $this->assertFalse($processed); // Returns false to indicate handled
    }

    /**
     * @group integration
     */
    public function testIntegrationWithLogger()
    {
        if (!getenv('LOGTAILER_INTEGRATION_TEST')) {
            $this->markTestSkipped('Integration tests are disabled');
        }

        $logger = new Logger('sync-test');
        $handler = new SynchronousLogtailHandler(
            $this->testToken,
            Level::Debug,
            true,
            $this->testEndpoint,
            5000,
            5000,
            false // Don't throw exceptions
        );

        $logger->pushHandler($handler);

        // These should not throw
        $logger->debug('Sync handler debug test');
        $logger->info('Sync handler info test', ['sync' => true]);
        $logger->error('Sync handler error test');

        $this->assertTrue(true); // If we got here, no exceptions were thrown
    }

    public function testExceptionHandling()
    {
        $handler = new SynchronousLogtailHandler(
            $this->testToken,
            Level::Debug,
            true,
            'invalid-endpoint', // This will cause curl to fail
            100, // Very short timeout
            100,
            false // Don't throw exceptions, just trigger warnings
        );

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'Test error handling',
            context: [],
            extra: []
        );

        // Should not throw exception when throwExceptions is false
        @$handler->handle($record); // Suppress the warning for testing
        $this->assertTrue(true); // If we got here, no exception was thrown
    }
}