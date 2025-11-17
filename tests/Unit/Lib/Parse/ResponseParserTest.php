<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Parse;

use ClaudePhp\Lib\Parse\ResponseParser;
use ClaudePhp\Responses\Message;
use PHPUnit\Framework\TestCase;

final class ResponseParserTest extends TestCase
{
    public function testParseSimpleJson(): void
    {
        $message = new Message(
            id: 'msg-123',
            type: 'message',
            role: 'assistant',
            content: [
                ['type' => 'text', 'text' => '{"name": "John", "age": 30}'],
            ],
            model: 'claude-sonnet-4-5-20250929',
            stop_reason: 'end_turn'
        );

        $schema = [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
            ],
            'required' => ['name', 'age'],
        ];

        $result = ResponseParser::parse($message, $schema);

        $this->assertSame('John', $result['name']);
        $this->assertSame(30, $result['age']);
    }

    public function testParseMissingRequired(): void
    {
        $message = new Message(
            id: 'msg-123',
            type: 'message',
            role: 'assistant',
            content: [
                ['type' => 'text', 'text' => '{"name": "John"}'],
            ],
            model: 'claude-sonnet-4-5-20250929',
            stop_reason: 'end_turn'
        );

        $schema = [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
            ],
            'required' => ['name', 'age'],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing required property');

        ResponseParser::parse($message, $schema);
    }

    public function testParseInvalidJson(): void
    {
        $message = new Message(
            id: 'msg-123',
            type: 'message',
            role: 'assistant',
            content: [
                ['type' => 'text', 'text' => 'Not valid JSON'],
            ],
            model: 'claude-sonnet-4-5-20250929',
            stop_reason: 'end_turn'
        );

        $schema = ['type' => 'object'];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse JSON');

        ResponseParser::parse($message, $schema);
    }
}
