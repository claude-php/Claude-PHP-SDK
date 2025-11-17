<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Streaming;

use ClaudePhp\Lib\Streaming\StructuredOutputStream;
use ClaudePhp\Responses\StreamResponse;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

class StructuredOutputStreamTest extends TestCase
{
    public function testParsesSnapshotsDuringStreaming(): void
    {
        $schema = [
            'type' => 'object',
            'required' => ['answer'],
            'properties' => [
                'answer' => ['type' => 'string'],
            ],
        ];

        $streamData = '';
        $streamData .= $this->event('message_start', [
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_structured',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [],
                'model' => 'claude',
                'usage' => ['input_tokens' => 0],
            ],
        ]);
        $streamData .= $this->event('content_block_start', [
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => ['type' => 'text', 'text' => ''],
        ]);
        $streamData .= $this->event('content_block_delta', [
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => ['type' => 'text_delta', 'text' => '{"answer": "Hel'],
        ]);
        $streamData .= $this->event('content_block_delta', [
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => ['type' => 'text_delta', 'text' => 'lo"}'],
        ]);
        $streamData .= "event: message_stop\ndata: [DONE]\n\n";

        $response = new StreamResponse(new Response(
            200,
            ['Content-Type' => 'text/event-stream'],
            $streamData
        ));

        $stream = new StructuredOutputStream($response, $schema);
        $events = iterator_to_array($stream);

        $this->assertArrayNotHasKey('parsed_output', $events[2]);
        $this->assertSame(['answer' => 'Hello'], $events[3]['parsed_output']);

        $final = $stream->getFinalMessage();
        $this->assertEquals('{"answer": "Hello"}', $final->content[0]['text']);
    }

    private function event(string $type, array $data): string
    {
        return "event: {$type}\ndata: " . json_encode($data) . "\n\n";
    }
}
