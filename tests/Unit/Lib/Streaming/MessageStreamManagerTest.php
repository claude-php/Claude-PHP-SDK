<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Streaming;

use ClaudePhp\Lib\Streaming\MessageStreamManager;
use PHPUnit\Framework\TestCase;

final class MessageStreamManagerTest extends TestCase
{
    public function testAddEventMessageStart(): void
    {
        $manager = new MessageStreamManager();

        $manager->addEvent([
            'type' => 'message_start',
            'message' => [
                'id' => 'msg-123',
                'role' => 'assistant',
                'content' => [],
            ],
        ]);

        $message = $manager->getMessage();

        $this->assertSame('msg-123', $message->id);
        $this->assertSame('assistant', $message->role);
    }

    public function testAddEventContentBlock(): void
    {
        $manager = new MessageStreamManager();

        $manager->addEvent([
            'type' => 'message_start',
            'message' => [
                'id' => 'msg-123',
                'role' => 'assistant',
                'content' => [],
            ],
        ]);

        $manager->addEvent([
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => [
                'type' => 'text',
                'text' => '',
            ],
        ]);

        $manager->addEvent([
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => [
                'type' => 'text_delta',
                'text' => 'Hello',
            ],
        ]);

        $message = $manager->getMessage();

        $this->assertCount(1, $message->content);
        $this->assertSame('text', $message->content[0]['type']);
        $this->assertStringContainsString('Hello', $message->content[0]['text']);
    }

    public function testAddEventMultipleBlocks(): void
    {
        $manager = new MessageStreamManager();

        $manager->addEvent([
            'type' => 'message_start',
            'message' => [
                'id' => 'msg-123',
                'role' => 'assistant',
                'content' => [],
            ],
        ]);

        $manager->addEvent([
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => ['type' => 'text', 'text' => ''],
        ]);

        $manager->addEvent([
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => ['type' => 'text_delta', 'text' => 'Response:'],
        ]);

        $manager->addEvent([
            'type' => 'content_block_start',
            'index' => 1,
            'content_block' => [
                'type' => 'tool_use',
                'id' => 'tool-123',
                'name' => 'get_time',
                'input' => '',
            ],
        ]);

        $message = $manager->getMessage();

        $this->assertCount(2, $message->content);
        $this->assertSame('text', $message->content[0]['type']);
        $this->assertSame('tool_use', $message->content[1]['type']);
    }

    public function testMessageDelta(): void
    {
        $manager = new MessageStreamManager();

        $manager->addEvent([
            'type' => 'message_start',
            'message' => [
                'id' => 'msg-123',
                'role' => 'assistant',
                'content' => [],
                'usage' => ['input_tokens' => 10],
            ],
        ]);

        $manager->addEvent([
            'type' => 'message_delta',
            'delta' => [
                'stop_reason' => 'tool_use',
            ],
            'usage' => ['output_tokens' => 5],
        ]);

        $message = $manager->getMessage();

        $this->assertSame('tool_use', $message->stop_reason);
        $this->assertSame(5, $message->usage->output_tokens);
    }
}
