<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Integration;

use ClaudePhp\Lib\Streaming\MessageStream;
use ClaudePhp\Responses\StreamResponse;
use ClaudePhp\Tests\TestCase;
use ClaudePhp\Tests\TestUtils;

/**
 * Comprehensive streaming tests
 * Equivalent to Python SDK's streaming functionality tests
 */
class StreamingTest extends TestCase
{
    public function testBasicStreamingFlow(): void
    {
        $streamData = '';
        
        // message_start event
        $streamData .= $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_test_123',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [],
                'model' => 'claude-sonnet-4-5-20250929',
                'stop_reason' => null,
                'stop_sequence' => null,
                'usage' => ['input_tokens' => 12, 'output_tokens' => 0],
            ],
        ]);

        // content_block_start event
        $streamData .= $this->createStreamingEvent('content_block_start', [
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => [
                'type' => 'text',
                'text' => '',
            ],
        ]);

        // Multiple content_block_delta events
        $textChunks = ['Hello', ', how', ' are', ' you', ' today', '?'];
        foreach ($textChunks as $chunk) {
            $streamData .= $this->createStreamingEvent('content_block_delta', [
                'type' => 'content_block_delta',
                'index' => 0,
                'delta' => [
                    'type' => 'text_delta',
                    'text' => $chunk,
                ],
            ]);
        }

        // content_block_stop event
        $streamData .= $this->createStreamingEvent('content_block_stop', [
            'type' => 'content_block_stop',
            'index' => 0,
        ]);

        // message_delta event
        $streamData .= $this->createStreamingEvent('message_delta', [
            'type' => 'message_delta',
            'delta' => [
                'stop_reason' => 'end_turn',
                'stop_sequence' => null,
            ],
            'usage' => [
                'output_tokens' => 6,
            ],
        ]);

        // message_stop event
        $streamData .= "event: message_stop\ndata: [DONE]\n\n";

        $this->addMockResponse(200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ], $streamData);

        $stream = $this->testClient->messages()->stream([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        $this->assertInstanceOf(StreamResponse::class, $stream);

        // Verify the stream contains all expected events
        $events = [];
        foreach ($stream as $event) {
            $events[] = $event;
        }

        $this->assertCount(10, $events); // 1 start + 1 content_start + 6 deltas + 1 content_stop + 1 message_delta = 10 events

        // Verify event types
        $this->assertEquals('message_start', $events[0]['type']);
        $this->assertEquals('content_block_start', $events[1]['type']);
        $this->assertEquals('content_block_delta', $events[2]['type']);
        $this->assertEquals('content_block_delta', $events[7]['type']);
        $this->assertEquals('content_block_stop', $events[8]['type']);
        $this->assertEquals('message_delta', $events[9]['type']);
    }

    public function testStreamingWithToolUse(): void
    {
        $streamData = '';
        
        // message_start
        $streamData .= $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_tool_test',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [],
                'model' => 'claude-sonnet-4-5-20250929',
                'stop_reason' => null,
                'stop_sequence' => null,
                'usage' => ['input_tokens' => 20, 'output_tokens' => 0],
            ],
        ]);

        // Tool use content block start
        $streamData .= $this->createStreamingEvent('content_block_start', [
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => [
                'type' => 'tool_use',
                'id' => 'toolu_test_123',
                'name' => 'calculator',
                'input' => new \stdClass(), // Start with empty object
            ],
        ]);

        // Tool input delta
        $streamData .= $this->createStreamingEvent('content_block_delta', [
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => [
                'type' => 'input_json_delta',
                'partial_json' => '{"operation": "add", "numbers": [2, 2]}',
            ],
        ]);

        // Tool content block stop
        $streamData .= $this->createStreamingEvent('content_block_stop', [
            'type' => 'content_block_stop',
            'index' => 0,
        ]);

        // message_delta with tool_use stop reason
        $streamData .= $this->createStreamingEvent('message_delta', [
            'type' => 'message_delta',
            'delta' => [
                'stop_reason' => 'tool_use',
                'stop_sequence' => null,
            ],
            'usage' => [
                'output_tokens' => 15,
            ],
        ]);

        // message_stop
        $streamData .= "event: message_stop\ndata: [DONE]\n\n";

        $this->addMockResponse(200, [
            'Content-Type' => 'text/event-stream',
        ], $streamData);

        $tools = [
            [
                'name' => 'calculator',
                'description' => 'Calculate mathematical expressions',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'operation' => ['type' => 'string'],
                        'numbers' => ['type' => 'array'],
                    ],
                ],
            ],
        ];

        $stream = $this->testClient->messages()->stream([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'tools' => $tools,
            'messages' => [
                ['role' => 'user', 'content' => 'Calculate 2 + 2'],
            ],
        ]);

        $events = iterator_to_array($stream);

        // Verify tool use events
        $this->assertEquals('tool_use', $events[1]['content_block']['type']);
        $this->assertEquals('calculator', $events[1]['content_block']['name']);
        $this->assertEquals('input_json_delta', $events[2]['delta']['type']);
        $this->assertEquals('tool_use', $events[4]['delta']['stop_reason']);
    }

    public function testStreamingErrorHandling(): void
    {
        // Create a stream that includes an error
        $streamData = $this->createStreamingEvent('error', [
            'type' => 'error',
            'error' => [
                'type' => 'invalid_request_error',
                'message' => 'Stream processing error',
            ],
        ]);

        $this->addMockResponse(200, [
            'Content-Type' => 'text/event-stream',
        ], $streamData);

        $stream = $this->testClient->messages()->stream([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        $events = iterator_to_array($stream);

        $this->assertCount(1, $events);
        $this->assertEquals('error', $events[0]['type']);
        $this->assertSame('invalid_request_error', $events[0]['error']['type']);
        $this->assertSame('Stream processing error', $events[0]['error']['message']);
    }

    public function testStreamingTracksServerToolUsage(): void
    {
        $streamData = '';

        $streamData .= $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_server_tool',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [],
                'model' => 'claude-sonnet-4-5-20250929',
                'usage' => [
                    'input_tokens' => 100,
                    'server_tool_use' => ['web_search_requests' => 0],
                ],
            ],
        ]);

        $streamData .= $this->createStreamingEvent('content_block_start', [
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => [
                'type' => 'web_search_tool_result',
                'content' => [],
            ],
        ]);

        $streamData .= $this->createStreamingEvent('content_block_stop', [
            'type' => 'content_block_stop',
            'index' => 0,
        ]);

        $streamData .= $this->createStreamingEvent('message_delta', [
            'type' => 'message_delta',
            'delta' => [
                'stop_reason' => 'end_turn',
            ],
            'usage' => [
                'output_tokens' => 20,
                'server_tool_use' => ['web_search_requests' => 1],
            ],
        ]);

        $streamData .= "event: message_stop\ndata: [DONE]\n\n";

        $this->addMockResponse(200, [
            'Content-Type' => 'text/event-stream',
        ], $streamData);

        $response = $this->testClient->messages()->stream([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 50,
            'messages' => [
                ['role' => 'user', 'content' => 'Search the web'],
            ],
        ]);

        $messageStream = new MessageStream($response);
        foreach ($messageStream as $ignored) {
            // Drain stream
        }

        $final = $messageStream->getFinalMessage();
        $this->assertNotNull($final->usage);
        $this->assertSame(1, $final->usage->server_tool_use['web_search_requests']);
        $this->assertSame(20, $final->usage->output_tokens);
        $this->assertSame(100, $final->usage->input_tokens);
    }

    public function testStreamingWithMultibyteCharacters(): void
    {
        $streamData = '';
        
        $streamData .= $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_multibyte',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [],
                'model' => 'claude-sonnet-4-5-20250929',
                'stop_reason' => null,
                'stop_sequence' => null,
                'usage' => ['input_tokens' => 10, 'output_tokens' => 0],
            ],
        ]);

        $streamData .= $this->createStreamingEvent('content_block_start', [
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => ['type' => 'text', 'text' => ''],
        ]);

        // Test with various multibyte characters
        $multibyteTexts = ['こんにちは', '🚀', '漢字', '🌟✨'];
        foreach ($multibyteTexts as $text) {
            $streamData .= $this->createStreamingEvent('content_block_delta', [
                'type' => 'content_block_delta',
                'index' => 0,
                'delta' => [
                    'type' => 'text_delta',
                    'text' => $text,
                ],
            ]);
        }

        $streamData .= $this->createStreamingEvent('content_block_stop', [
            'type' => 'content_block_stop',
            'index' => 0,
        ]);

        $streamData .= "event: message_stop\ndata: [DONE]\n\n";

        $this->addMockResponse(200, [
            'Content-Type' => 'text/event-stream; charset=utf-8',
        ], $streamData);

        $stream = $this->testClient->messages()->stream([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Say hello in Japanese with emojis'],
            ],
        ]);

        $events = iterator_to_array($stream);

        // Verify multibyte characters are preserved
        $textDeltas = array_filter($events, fn($event) => 
            isset($event['delta']['type']) && $event['delta']['type'] === 'text_delta'
        );

        $this->assertCount(4, $textDeltas);
        
        $texts = array_map(fn($event) => $event['delta']['text'], $textDeltas);
        $this->assertContains('こんにちは', $texts);
        $this->assertContains('🚀', $texts);
        $this->assertContains('漢字', $texts);
        $this->assertContains('🌟✨', $texts);
    }

    public function testStreamingEventValidation(): void
    {
        // Create various streaming events and validate their structure
        $events = [
            'message_start' => [
                'type' => 'message_start',
                'message' => [
                    'id' => 'msg_123',
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [],
                    'model' => 'claude-sonnet-4-5-20250929',
                ],
            ],
            'content_block_start' => [
                'type' => 'content_block_start',
                'index' => 0,
                'content_block' => ['type' => 'text', 'text' => ''],
            ],
            'content_block_delta' => [
                'type' => 'content_block_delta',
                'index' => 0,
                'delta' => ['type' => 'text_delta', 'text' => 'Hello'],
            ],
            'content_block_stop' => [
                'type' => 'content_block_stop',
                'index' => 0,
            ],
            'message_delta' => [
                'type' => 'message_delta',
                'delta' => ['stop_reason' => 'end_turn'],
                'usage' => ['output_tokens' => 5],
            ],
        ];

        foreach ($events as $eventType => $eventData) {
            $eventString = $this->createStreamingEvent($eventType, $eventData);
            TestUtils::assertStreamingEvent($eventString, $eventType);
        }
    }

    public function testStreamingWithCitation(): void
    {
        $streamData = '';
        
        $streamData .= $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_citation',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [],
                'model' => 'claude-sonnet-4-5-20250929',
            ],
        ]);

        $streamData .= $this->createStreamingEvent('content_block_start', [
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => ['type' => 'text', 'text' => ''],
        ]);

        // Text with citation delta
        $streamData .= $this->createStreamingEvent('content_block_delta', [
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => [
                'type' => 'text_delta',
                'text' => 'According to the document',
            ],
        ]);

        // Citation delta
        $streamData .= $this->createStreamingEvent('content_block_delta', [
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => [
                'type' => 'citations_delta',
                'citations' => [
                    [
                        'type' => 'citation',
                        'start' => 0,
                        'end' => 25,
                        'location' => [
                            'type' => 'page',
                            'page_number' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $streamData .= "event: message_stop\ndata: [DONE]\n\n";

        $this->addMockResponse(200, [
            'Content-Type' => 'text/event-stream',
        ], $streamData);

        $stream = $this->testClient->messages()->stream([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'What does the document say?'],
            ],
        ]);

        $events = iterator_to_array($stream);

        // Find citation delta event
        $citationEvent = array_filter($events, fn($event) => 
            isset($event['delta']['type']) && $event['delta']['type'] === 'citations_delta'
        );

        $this->assertNotEmpty($citationEvent);
        
        $citationData = array_values($citationEvent)[0];
        $this->assertIsArray($citationData['delta']['citations']);
        $this->assertEquals('page', $citationData['delta']['citations'][0]['location']['type']);
        $this->assertEquals(1, $citationData['delta']['citations'][0]['location']['page_number']);
    }

    public function testStreamResourceCleanup(): void
    {
        $streamData = $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => ['id' => 'test'],
        ]) . "event: message_stop\ndata: [DONE]\n\n";

        $this->addMockResponse(200, [
            'Content-Type' => 'text/event-stream',
        ], $streamData);

        $stream = $this->testClient->messages()->stream([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Test cleanup'],
            ],
        ]);

        // Consume stream
        foreach ($stream as $event) {
            // Process events
        }

        // Verify stream is properly closed/cleaned up
        // This would typically involve checking if resources are released
        // For now, we just verify no exceptions are thrown during cleanup
        unset($stream);
        $this->assertTrue(true); // If we reach here, cleanup succeeded
    }
}
