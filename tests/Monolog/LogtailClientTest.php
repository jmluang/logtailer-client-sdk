<?php

namespace Jmluang\Logtailer\Tests\Monolog;

use PHPUnit\Framework\TestCase;
use Jmluang\Logtailer\Monolog\LogtailClient;

class LogtailClientTest extends TestCase
{
    private string $testToken = 'test-token-123';
    private string $testEndpoint = 'http://localhost:8080/logs';

    public function testConstructorRequiresCurlExtension()
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('curl extension is not loaded');
        }

        $client = new LogtailClient($this->testToken, $this->testEndpoint);
        $this->assertInstanceOf(LogtailClient::class, $client);
    }

    public function testDefaultEndpoint()
    {
        $client = new LogtailClient($this->testToken);
        $this->assertInstanceOf(LogtailClient::class, $client);
    }

    public function testCustomTimeouts()
    {
        $client = new LogtailClient(
            $this->testToken,
            $this->testEndpoint,
            1000, // connectionTimeoutMs
            2000  // timeoutMs
        );
        $this->assertInstanceOf(LogtailClient::class, $client);
    }

    public function testConstantsAreDefined()
    {
        $this->assertEquals('https://in.logs.betterstack.com', LogtailClient::URL);
        $this->assertEquals(5000, LogtailClient::DEFAULT_CONNECTION_TIMEOUT_MILLISECONDS);
        $this->assertEquals(5000, LogtailClient::DEFAULT_TIMEOUT_MILLISECONDS);
    }

    /**
     * @group integration
     */
    public function testSendMethod()
    {
        if (!getenv('LOGTAILER_INTEGRATION_TEST')) {
            $this->markTestSkipped('Integration tests are disabled');
        }

        $client = new LogtailClient($this->testToken, $this->testEndpoint);

        $testData = json_encode([
            'dt' => date('c'),
            'level' => 'INFO',
            'message' => 'PHPUnit test message',
            'monolog' => [
                'channel' => 'test',
                'context' => [],
                'extra' => []
            ]
        ]);

        // This should not throw an exception
        $this->assertNull($client->send($testData));
    }
}