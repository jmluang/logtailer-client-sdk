<?php

namespace Jmluang\Logtailer\Tests\Monolog;

use PHPUnit\Framework\TestCase;
use Jmluang\Logtailer\Monolog\LogtailHandlerBuilder;
use Jmluang\Logtailer\Monolog\LogtailHandler;
use Monolog\Level;

class LogtailHandlerBuilderTest extends TestCase
{
    private string $testToken = 'test-token-123';
    private string $testEndpoint = 'http://localhost:8080/logs';

    public function testBuilderCreation()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken);
        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithEndpoint()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withEndpoint($this->testEndpoint);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithLevel()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withLevel(Level::Warning);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithLogBubbling()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withLogBubbling(false);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithBufferLimit()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withBufferLimit(500);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithFlushOnOverflow()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withFlushOnOverflow(false);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithConnectionTimeout()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withConnectionTimeoutMilliseconds(3000);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithTimeout()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withTimeoutMilliseconds(10000);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithFlushInterval()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withFlushIntervalMilliseconds(2000);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithDisabledFlushInterval()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withFlushIntervalMilliseconds(null);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderWithExceptionThrowing()
    {
        $builder = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withExceptionThrowing(true);

        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder);
    }

    public function testBuilderIsImmutable()
    {
        $builder1 = LogtailHandlerBuilder::withSourceToken($this->testToken);
        $builder2 = $builder1->withEndpoint($this->testEndpoint);

        $this->assertNotSame($builder1, $builder2);
        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder1);
        $this->assertInstanceOf(LogtailHandlerBuilder::class, $builder2);
    }

    public function testBuildHandler()
    {
        $handler = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withEndpoint($this->testEndpoint)
            ->withLevel(Level::Info)
            ->withLogBubbling(true)
            ->withBufferLimit(200)
            ->withFlushOnOverflow(true)
            ->withConnectionTimeoutMilliseconds(2000)
            ->withTimeoutMilliseconds(3000)
            ->withFlushIntervalMilliseconds(1000)
            ->withExceptionThrowing(false)
            ->build();

        $this->assertInstanceOf(LogtailHandler::class, $handler);
    }

    public function testBuildHandlerWithDefaults()
    {
        $handler = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->build();

        $this->assertInstanceOf(LogtailHandler::class, $handler);
    }

    public function testChainedBuilderMethods()
    {
        $handler = LogtailHandlerBuilder::withSourceToken($this->testToken)
            ->withEndpoint($this->testEndpoint)
            ->withLevel(Level::Debug)
            ->withLogBubbling(false)
            ->withBufferLimit(100)
            ->withFlushOnOverflow(false)
            ->withConnectionTimeoutMilliseconds(1000)
            ->withTimeoutMilliseconds(2000)
            ->withFlushIntervalMilliseconds(500)
            ->withExceptionThrowing(true)
            ->build();

        $this->assertInstanceOf(LogtailHandler::class, $handler);
    }
}