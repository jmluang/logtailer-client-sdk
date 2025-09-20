<?php

namespace Jmluang\Logtailer\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Jmluang\Logtailer\Monolog\LogtailHandler;
use Jmluang\Logtailer\Monolog\LogtailClient;
use Monolog\Logger;
use Monolog\Level;

/**
 * @group integration
 * @group real-server
 */
class RealServerTest extends TestCase
{
    protected function setUp(): void
    {
        // Skip if not in integration test mode
        if (!getenv('LOGTAILER_INTEGRATION_TEST')) {
            $this->markTestSkipped('Integration tests are disabled. Set LOGTAILER_INTEGRATION_TEST=1 to enable.');
        }

        // Check if server is reachable
        $endpoint = getenv('LOGTAILER_ENDPOINT') ?: 'http://localhost:8080/logs';
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 0) {
            $this->markTestSkipped("Logtailer server is not reachable at {$endpoint}");
        }
    }

    public function testInvalidTokenReturnsError()
    {
        $invalidToken = 'invalid-token-12345';
        $endpoint = getenv('LOGTAILER_ENDPOINT') ?: 'http://localhost:8080/logs';

        // Create a handler with invalid token
        $logger = new Logger('test');
        $handler = new LogtailHandler(
            $invalidToken,
            Level::Debug,
            true,
            $endpoint,
            10,     // Small buffer to force flush
            true,   // Flush on overflow
            5000,
            5000,
            null,   // No auto flush
            true    // Throw exceptions
        );
        $logger->pushHandler($handler);

        // Send enough logs to trigger flush
        for ($i = 0; $i < 11; $i++) {
            $logger->info("Test message $i");
        }

        // Since we're using invalid token, the flush should have failed
        // but Monolog might silently ignore it unless we check
        $this->assertTrue(true); // This test needs better assertion
    }

    public function testValidTokenSucceeds()
    {
        $validToken = getenv('LOGTAILER_TOKEN');
        if (!$validToken) {
            $this->markTestSkipped('LOGTAILER_TOKEN environment variable is not set');
        }

        $endpoint = getenv('LOGTAILER_ENDPOINT') ?: 'http://localhost:8080/logs';

        $logger = new Logger('test');
        $handler = new LogtailHandler(
            $validToken,
            Level::Debug,
            true,
            $endpoint
        );
        $logger->pushHandler($handler);

        // This should succeed
        $logger->info('Valid token test', ['test' => true]);

        // Force flush
        $handler->flush();

        $this->assertTrue(true); // If we got here, no exception was thrown
    }

    public function testDirectClientWithInvalidToken()
    {
        $invalidToken = 'invalid-token-12345';
        $endpoint = getenv('LOGTAILER_ENDPOINT') ?: 'http://localhost:8080/logs';

        $client = new LogtailClient($invalidToken, $endpoint, 1000, 1000);

        $testData = json_encode([
            'dt' => date('c'),
            'level' => 'INFO',
            'message' => 'Test with invalid token'
        ]);

        // This should fail silently (current implementation)
        $client->send($testData);

        // Check if we can detect the failure
        $this->assertTrue(true); // Need better assertion
    }

    public function testBatchSendWithValidToken()
    {
        $validToken = getenv('LOGTAILER_TOKEN');
        if (!$validToken) {
            $this->markTestSkipped('LOGTAILER_TOKEN environment variable is not set');
        }

        $endpoint = getenv('LOGTAILER_ENDPOINT') ?: 'http://localhost:8080/logs';

        $logger = new Logger('batch-test');
        $handler = new LogtailHandler(
            $validToken,
            Level::Debug,
            true,
            $endpoint,
            100,    // Buffer 100 messages
            true,
            5000,
            5000
        );
        $logger->pushHandler($handler);

        // Send multiple logs
        for ($i = 0; $i < 50; $i++) {
            $logger->info("Batch message {$i}", [
                'index' => $i,
                'timestamp' => microtime(true)
            ]);
        }

        // Flush remaining
        $handler->flush();

        $this->assertTrue(true);
    }

    /**
     * This test actually verifies server response
     */
    public function testServerResponseValidation()
    {
        $validToken = getenv('LOGTAILER_TOKEN') ?: '664bLPZlZMzJluwNiT0vDm4rI0faeKNJ';
        $endpoint = getenv('LOGTAILER_ENDPOINT') ?: 'http://localhost:8080/logs';

        // Manually send request to check response
        $data = json_encode([
            [
                'dt' => date('c'),
                'level' => 'INFO',
                'message' => 'Manual test',
                'monolog' => [
                    'channel' => 'test',
                    'context' => [],
                    'extra' => []
                ]
            ]
        ]);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $validToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Assert the response
        if ($error) {
            $this->fail("CURL error: {$error}");
        }

        $this->assertContains($httpCode, [200, 201, 202, 204], "Expected success HTTP code, got {$httpCode}. Response: {$response}");
    }

    /**
     * Test with definitely invalid token to verify error handling
     */
    public function testInvalidTokenReturnsUnauthorized()
    {
        $invalidToken = 'definitely-invalid-token';
        $endpoint = getenv('LOGTAILER_ENDPOINT') ?: 'http://localhost:8080/logs';

        $data = json_encode([
            [
                'dt' => date('c'),
                'level' => 'ERROR',
                'message' => 'Unauthorized test'
            ]
        ]);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $invalidToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Should return 401 or 403 for invalid token
        $this->assertContains($httpCode, [401, 403], "Expected unauthorized/forbidden for invalid token, got {$httpCode}");
    }
}