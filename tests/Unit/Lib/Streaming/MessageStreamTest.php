<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Streaming;

use ClaudePhp\Lib\Streaming\MessageStream;
use PHPUnit\Framework\TestCase;

final class MessageStreamTest extends TestCase
{
    public function testIterator(): void
    {
        $events = [
            ['type' => 'message_start', 'message' => ['id' => 'msg-123', 'role' => 'assistant', 'content' => []]],
            ['type' => 'content_block_start', 'index' => 0, 'content_block' => ['type' => 'text', 'text' => '']],
            ['type' => 'content_block_delta', 'index' => 0, 'delta' => ['type' => 'text_delta', 'text' => 'Hello']],
            ['type' => 'message_stop'],
        ];

        $stream = new MessageStream($events);

        $count = 0;
        foreach ($stream as $event) {
            $this->assertIsArray($event);
            ++$count;
        }

        $this->assertSame(4, $count);
        $this->assertNotNull($stream->finalMessage());
    }

    public function testGetTextContent(): void
    {
        $events = [
            ['type' => 'message_start', 'message' => ['id' => 'msg-123', 'role' => 'assistant', 'content' => []]],
            ['type' => 'content_block_start', 'index' => 0, 'content_block' => ['type' => 'text', 'text' => '']],
            ['type' => 'content_block_delta', 'index' => 0, 'delta' => ['type' => 'text_delta', 'text' => 'Hello ']],
            ['type' => 'content_block_delta', 'index' => 0, 'delta' => ['type' => 'text_delta', 'text' => 'World']],
            ['type' => 'message_stop'],
        ];

        $stream = new MessageStream($events);

        foreach ($stream as $_) {
            // iterate to consume
        }

        $text = $stream->textStream();
        $this->assertStringContainsString('Hello', $text);
        $this->assertStringContainsString('World', $text);
        $this->assertNotNull($stream->finalMessage());
    }

    public function testGetFinalMessageConsumesRemainingEvents(): void
    {
        $events = [
            ['type' => 'message_start', 'message' => ['id' => 'msg-1', 'role' => 'assistant', 'content' => []]],
            ['type' => 'message_stop'],
        ];

        $stream = new MessageStream($events);

        $message = $stream->getFinalMessage();
        $this->assertSame('msg-1', $message->id);
    }
}
