<?php

namespace Jmluang\Logtailer\Tests\Monolog;

use PHPUnit\Framework\TestCase;
use Jmluang\Logtailer\Monolog\LogtailHandler;
use Monolog\Logger;
use Monolog\Level;
use Monolog\LogRecord;

class LogtailHandlerTest extends TestCase
{
    private string $testToken = 'test-token-123';
    private string $testEndpoint = 'http://localhost:8080/logs';

    public function testHandlerConstructor()
    {
        $handler = new LogtailHandler(
            $this->testToken,
            Level::Debug,
            true,
            $this->testEndpoint
        );

        $this->assertInstanceOf(LogtailHandler::class, $handler);
    }

    public function testDefaultConstants()
    {
        $this->assertTrue(LogtailHandler::DEFAULT_BUBBLE);
        $this->assertEquals(1000, LogtailHandler::DEFAULT_BUFFER_LIMIT);
        $this->assertTrue(LogtailHandler::DEFAULT_FLUSH_ON_OVERFLOW);
        $this->assertEquals(5000, LogtailHandler::DEFAULT_FLUSH_INTERVAL_MILLISECONDS);
    }

    public function testHandlerWithCustomBufferLimit()
    {
        $handler = new LogtailHandler(
            $this->testToken,
            Level::Info,
            true,
            $this->testEndpoint,
            500 // Custom buffer limit
        );

        $this->assertInstanceOf(LogtailHandler::class, $handler);
    }

    public function testHandlerWithDisabledFlushInterval()
    {
        $handler = new LogtailHandler(
            $this->testToken,
            Level::Warning,
            true,
            $this->testEndpoint,
            1000,
            true,
            5000,
            5000,
            null // Disabled flush interval
        );

        $this->assertInstanceOf(LogtailHandler::class, $handler);
    }

    public function testHandleRecord()
    {
        $handler = new LogtailHandler(
            $this->testToken,
            Level::Debug,
            false,
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
        $this->assertTrue($result); // Returns true even with bubble = false due to parent BufferHandler behavior
    }

    public function testHandleRecordWithBubble()
    {
        $handler = new LogtailHandler(
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
        $this->assertFalse($result); // BufferHandler returns opposite of bubble when handling
    }

    public function testHandleIgnoresLowLevelRecords()
    {
        $handler = new LogtailHandler(
            $this->testToken,
            Level::Warning, // Minimum level is Warning
            false,
            $this->testEndpoint
        );

        $debugRecord = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Debug, // Lower than Warning
            message: 'Debug message',
            context: [],
            extra: []
        );

        $result = $handler->handle($debugRecord);
        $this->assertFalse($result); // Returns false when record is below minimum level and not handled

        $errorRecord = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error, // Higher than Warning
            message: 'Error message',
            context: [],
            extra: []
        );

        $result = $handler->handle($errorRecord);
        $this->assertTrue($result); // BufferHandler returns opposite of bubble
    }

    public function testFlushMethod()
    {
        $handler = new LogtailHandler(
            $this->testToken,
            Level::Debug,
            true,
            $this->testEndpoint
        );

        // Add a record
        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test flush',
            context: [],
            extra: []
        );

        $handler->handle($record);

        // Flush should not throw
        $this->assertNull($handler->flush());
    }

    /**
     * @group integration
     */
    public function testIntegrationWithLogger()
    {
        if (!getenv('LOGTAILER_INTEGRATION_TEST')) {
            $this->markTestSkipped('Integration tests are disabled');
        }

        $logger = new Logger('test-logger');
        $handler = new LogtailHandler(
            $this->testToken,
            Level::Debug,
            true,
            $this->testEndpoint
        );

        $logger->pushHandler($handler);

        // These should not throw
        $logger->debug('PHPUnit debug test');
        $logger->info('PHPUnit info test', ['context' => 'value']);
        $logger->warning('PHPUnit warning test');
        $logger->error('PHPUnit error test', ['error_code' => 123]);

        $this->assertTrue(true); // If we got here, no exceptions were thrown
    }
}