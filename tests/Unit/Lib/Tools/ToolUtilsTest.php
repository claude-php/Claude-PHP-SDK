<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Lib\Tools;

use ClaudePhp\Lib\Tools\ToolRunner;
use ClaudePhp\Lib\Tools\ToolUtils;
use PHPUnit\Framework\TestCase;

class ToolUtilsTest extends TestCase
{
    public function testDefineTool(): void
    {
        $tool = ToolUtils::defineTool(
            'get_weather',
            'Get current weather',
            [
                'type' => 'object',
                'properties' => [
                    'location' => ['type' => 'string'],
                ],
                'required' => ['location'],
            ]
        );

        $this->assertSame('get_weather', $tool['name']);
        $this->assertSame('Get current weather', $tool['description']);
        $this->assertSame('object', $tool['input_schema']['type']);
    }

    public function testSimpleStringTool(): void
    {
        $tool = ToolUtils::simpleStringTool('echo', 'Echo input');

        $this->assertSame('echo', $tool['name']);
        $this->assertSame('Echo input', $tool['description']);
        $this->assertSame('object', $tool['input_schema']['type']);
        $this->assertTrue(\in_array('input', $tool['input_schema']['required'], true));
    }

    public function testExtractToolUses(): void
    {
        $content = [
            ['type' => 'text', 'text' => 'Hello'],
            ['type' => 'tool_use', 'id' => '123', 'name' => 'get_time', 'input' => []],
            ['type' => 'text', 'text' => 'World'],
        ];

        $toolUses = ToolUtils::extractToolUses($content);

        $this->assertCount(1, $toolUses);
        $this->assertSame('tool_use', $toolUses[0]['type']);
        $this->assertSame('get_time', $toolUses[0]['name']);
    }

    public function testHasToolUse(): void
    {
        $content = [
            ['type' => 'text', 'text' => 'Hello'],
            ['type' => 'tool_use', 'id' => '123', 'name' => 'get_time', 'input' => []],
        ];

        $this->assertTrue(ToolUtils::hasToolUse($content));

        $contentNoTools = [
            ['type' => 'text', 'text' => 'Hello'],
        ];

        $this->assertFalse(ToolUtils::hasToolUse($contentNoTools));
    }

    public function testBuildToolResult(): void
    {
        $result = ToolUtils::buildToolResult('tool-123', 'Result content');

        $this->assertSame('tool_result', $result['type']);
        $this->assertSame('tool-123', $result['tool_use_id']);
        $this->assertSame('Result content', $result['content']);
        $this->assertFalse($result['is_error']);
    }

    public function testBuildToolResultError(): void
    {
        $result = ToolUtils::buildToolResult('tool-123', 'Error occurred', true);

        $this->assertSame('tool_result', $result['type']);
        $this->assertTrue($result['is_error']);
    }

    public function testBuildToolResultMessage(): void
    {
        $toolResults = [
            ToolUtils::buildToolResult('id1', 'Result 1'),
            ToolUtils::buildToolResult('id2', 'Result 2'),
        ];

        $message = ToolUtils::buildToolResultMessage($toolResults);

        $this->assertSame('user', $message['role']);
        $this->assertCount(2, $message['content']);
    }
}
