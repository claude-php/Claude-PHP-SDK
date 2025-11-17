<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Responses\Helpers;

use ClaudePhp\Responses\Helpers\MessageContentHelper;
use PHPUnit\Framework\TestCase;

final class MessageContentHelperTest extends TestCase
{
    public function testHydratesContentIntoTypedBlocks(): void
    {
        $message = [
            'content' => [
                ['type' => 'text', 'text' => 'Hello'],
                ['type' => 'tool_use', 'id' => '1', 'name' => 'get_weather', 'input' => ['city' => 'SF']],
                ['type' => 'tool_result', 'tool_use_id' => '1', 'content' => 'It is sunny'],
            ],
        ];

        $texts = MessageContentHelper::textBlocks($message);
        $this->assertCount(1, $texts);
        $this->assertSame('Hello', $texts[0]->text);

        $uses = MessageContentHelper::toolUses($message);
        $this->assertCount(1, $uses);
        $this->assertSame('get_weather', $uses[0]->name);
        $this->assertSame(['city' => 'SF'], $uses[0]->input);

        $results = MessageContentHelper::toolResults($message);
        $this->assertCount(1, $results);
        $this->assertSame('1', $results[0]->tool_use_id);
        $this->assertSame('It is sunny', $results[0]->content);
    }

    public function testConcatenatesText(): void
    {
        $message = [
            'content' => [
                ['type' => 'text', 'text' => 'Line 1'],
                ['type' => 'text', 'text' => 'Line 2'],
            ],
        ];

        $this->assertSame("Line 1\nLine 2", MessageContentHelper::text($message, "\n"));
    }
}
