<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Tools;

use ClaudePhp\Lib\Tools\Mcp;
use ClaudePhp\Lib\Tools\UnsupportedMcpValueException;
use PHPUnit\Framework\TestCase;

class McpTest extends TestCase
{
    public function testToolConversion(): void
    {
        $mcpTool = [
            'name' => 'get_weather',
            'description' => 'Get weather info',
            'inputSchema' => ['type' => 'object', 'properties' => ['city' => ['type' => 'string']]],
        ];

        $result = Mcp::tool($mcpTool);
        $this->assertSame('get_weather', $result['name']);
        $this->assertSame('Get weather info', $result['description']);
        $this->assertArrayHasKey('input_schema', $result);
    }

    public function testToolWithOptions(): void
    {
        $result = Mcp::tool(
            ['name' => 'test', 'description' => 'Test'],
            ['cache_control' => ['type' => 'ephemeral'], 'strict' => true],
        );
        $this->assertSame(['type' => 'ephemeral'], $result['cache_control']);
        $this->assertTrue($result['strict']);
    }

    public function testTextContentConversion(): void
    {
        $result = Mcp::content(['type' => 'text', 'text' => 'Hello']);
        $this->assertSame('text', $result['type']);
        $this->assertSame('Hello', $result['text']);
    }

    public function testContentWithCacheControl(): void
    {
        $result = Mcp::content(['type' => 'text', 'text' => 'x'], ['type' => 'ephemeral']);
        $this->assertSame(['type' => 'ephemeral'], $result['cache_control']);
    }

    public function testUnsupportedContentType(): void
    {
        $this->expectException(UnsupportedMcpValueException::class);
        Mcp::content(['type' => 'audio', 'data' => '...']);
    }

    public function testMessageConversion(): void
    {
        $result = Mcp::message(['role' => 'user', 'content' => 'Hello']);
        $this->assertSame('user', $result['role']);
        $this->assertCount(1, $result['content']);
        $this->assertSame('Hello', $result['content'][0]['text']);
    }

    public function testResourceToContent(): void
    {
        $result = Mcp::resourceToContent([
            'contents' => [
                ['text' => 'file contents', 'uri' => 'file:///test.txt'],
            ],
        ]);
        $this->assertCount(1, $result);
        $this->assertSame('text', $result[0]['type']);
        $this->assertSame('file contents', $result[0]['text']);
    }

    public function testResourceToFile(): void
    {
        $result = Mcp::resourceToFile([
            'contents' => [
                ['text' => 'data', 'uri' => 'file:///docs/readme.md', 'mimeType' => 'text/markdown'],
            ],
        ]);
        $this->assertCount(1, $result);
        $this->assertSame('readme.md', $result[0]['filename']);
        $this->assertSame('data', $result[0]['data']);
        $this->assertSame('text/markdown', $result[0]['mimeType']);
    }
}
