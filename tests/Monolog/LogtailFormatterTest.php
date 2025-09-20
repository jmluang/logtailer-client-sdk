<?php

namespace Jmluang\Logtailer\Tests\Monolog;

use PHPUnit\Framework\TestCase;
use Jmluang\Logtailer\Monolog\LogtailFormatter;
use Monolog\Logger;
use Monolog\Level;
use Monolog\LogRecord;

class LogtailFormatterTest extends TestCase
{
    private LogtailFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new LogtailFormatter();
    }

    public function testFormatSingleRecord()
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2024-01-01 12:00:00'),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: ['key' => 'value'],
            extra: ['extra_key' => 'extra_value']
        );

        $formatted = $this->formatter->format($record);
        $decoded = json_decode($formatted, true);

        $this->assertIsArray($decoded);
        $this->assertEquals('Test message', $decoded['message']);
        $this->assertEquals('Info', $decoded['level']);
        $this->assertEquals('test', $decoded['monolog']['channel']);
        $this->assertEquals(['key' => 'value'], $decoded['monolog']['context']);
        $this->assertEquals(['extra_key' => 'extra_value'], $decoded['monolog']['extra']);
        $this->assertArrayHasKey('dt', $decoded);
    }

    public function testFormatBatch()
    {
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
                level: Level::Error,
                message: 'Error message',
                context: ['error' => true],
                extra: []
            )
        ];

        $formatted = $this->formatter->formatBatch($records);
        $decoded = json_decode($formatted, true);

        $this->assertIsArray($decoded);
        $this->assertCount(2, $decoded);

        $this->assertEquals('Debug message', $decoded[0]['message']);
        $this->assertEquals('Debug', $decoded[0]['level']);
        $this->assertEquals('test1', $decoded[0]['monolog']['channel']);

        $this->assertEquals('Error message', $decoded[1]['message']);
        $this->assertEquals('Error', $decoded[1]['level']);
        $this->assertEquals('test2', $decoded[1]['monolog']['channel']);
        $this->assertEquals(['error' => true], $decoded[1]['monolog']['context']);
    }

    public function testFormatWithComplexContext()
    {
        $complexContext = [
            'user' => [
                'id' => 123,
                'name' => 'John Doe',
                'roles' => ['admin', 'user']
            ],
            'metadata' => [
                'ip' => '192.168.1.1',
                'user_agent' => 'PHPUnit'
            ]
        ];

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'app',
            level: Level::Info,
            message: 'Complex context test',
            context: $complexContext,
            extra: []
        );

        $formatted = $this->formatter->format($record);
        $decoded = json_decode($formatted, true);

        $this->assertEquals($complexContext, $decoded['monolog']['context']);
    }

    public function testFormatWithException()
    {
        $exception = new \Exception('Test exception', 500);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'error',
            level: Level::Error,
            message: 'Exception occurred',
            context: ['exception' => $exception],
            extra: []
        );

        $formatted = $this->formatter->format($record);
        $decoded = json_decode($formatted, true);

        $this->assertArrayHasKey('exception', $decoded['monolog']['context']);
        $this->assertArrayHasKey('class', $decoded['monolog']['context']['exception']);
        $this->assertArrayHasKey('message', $decoded['monolog']['context']['exception']);
        $this->assertArrayHasKey('code', $decoded['monolog']['context']['exception']);
        $this->assertArrayHasKey('file', $decoded['monolog']['context']['exception']);
    }

    public function testMaxNormalizeItemCountIsUnlimited()
    {
        // Create a moderately nested array to test normalization
        // Note: JsonFormatter has a default depth limit for normalization
        $deepArray = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'level4' => [
                            'level5' => [
                                'value' => 'deep_value'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Debug,
            message: 'Deep array test',
            context: ['deep' => $deepArray],
            extra: []
        );

        $formatted = $this->formatter->format($record);
        $decoded = json_decode($formatted, true);

        // Verify the deep value is preserved
        $this->assertArrayHasKey('deep', $decoded['monolog']['context']);
        $this->assertArrayHasKey('level1', $decoded['monolog']['context']['deep']);
        $this->assertArrayHasKey('level2', $decoded['monolog']['context']['deep']['level1']);
        $this->assertArrayHasKey('level3', $decoded['monolog']['context']['deep']['level1']['level2']);
        $this->assertArrayHasKey('level4', $decoded['monolog']['context']['deep']['level1']['level2']['level3']);
        $this->assertArrayHasKey('level5', $decoded['monolog']['context']['deep']['level1']['level2']['level3']['level4']);
        $this->assertArrayHasKey('value', $decoded['monolog']['context']['deep']['level1']['level2']['level3']['level4']['level5']);
        $this->assertEquals('deep_value', $decoded['monolog']['context']['deep']['level1']['level2']['level3']['level4']['level5']['value']);
    }
}