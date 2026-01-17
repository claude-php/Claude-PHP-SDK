<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Tools;

use ClaudePhp\Lib\Tools\BetaToolRunner;
use ClaudePhp\Lib\Tools\ToolRunner;
use ClaudePhp\Lib\Tools\StreamingToolRunner;
use ClaudePhp\Tests\TestCase;

/**
 * Tests for server-side tools support in tool runners
 *
 * Based on Python SDK v0.76.0 feature: add support for server-side tools (#1086)
 */
class ServerSideToolsTest extends TestCase
{
    public function testBetaToolRunnerHandlesServerToolUse(): void
    {
        // Create a tool response with server_tool_use blocks
        $toolResponse = json_encode([
            'id' => 'msg_test_server_tools',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'Let me execute that code for you.',
                ],
                [
                    'type' => 'server_tool_use',
                    'id' => 'toolu_server_001',
                    'name' => 'code_execution',
                    'input' => [
                        'language' => 'python',
                        'code' => 'print("Hello from server!")',
                    ],
                ],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'tool_use',
            'usage' => [
                'input_tokens' => 100,
                'output_tokens' => 50,
            ],
        ]);

        // Final response after server tool execution
        $finalResponse = json_encode([
            'id' => 'msg_test_final',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'The code executed successfully and printed: Hello from server!',
                ],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 150,
                'output_tokens' => 30,
            ],
        ]);

        $this->addMockResponses([
            ['status' => 200, 'body' => $toolResponse],
            ['status' => 200, 'body' => $finalResponse],
        ]);

        $runner = $this->testClient->beta()->messages()->toolRunner([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Execute some Python code'],
            ],
        ], []);

        $messages = iterator_to_array($runner);

        // Should get 2 messages
        $this->assertCount(2, $messages);

        // First message should contain server_tool_use
        $firstContent = $messages[0]->content;
        $hasServerTool = false;
        foreach ($firstContent as $block) {
            if (($block['type'] ?? '') === 'server_tool_use') {
                $hasServerTool = true;
                $this->assertEquals('code_execution', $block['name']);
            }
        }
        $this->assertTrue($hasServerTool, 'First message should contain server_tool_use block');

        // Verify we made the right API calls
        $requests = $this->getAllRequests();
        $this->assertCount(2, $requests);
    }

    public function testToolRunnerHandlesMixedClientAndServerTools(): void
    {
        // Response with both client and server tools
        $mixedToolResponse = json_encode([
            'id' => 'msg_test_mixed',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'tool_use',
                    'id' => 'toolu_client_001',
                    'name' => 'get_weather',
                    'input' => ['location' => 'San Francisco'],
                ],
                [
                    'type' => 'server_tool_use',
                    'id' => 'toolu_server_001',
                    'name' => 'code_execution',
                    'input' => ['code' => 'x = 1 + 1'],
                ],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'tool_use',
            'usage' => [
                'input_tokens' => 100,
                'output_tokens' => 50,
            ],
        ]);

        $finalResponse = json_encode([
            'id' => 'msg_test_final',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                ['type' => 'text', 'text' => 'Done!'],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 150,
                'output_tokens' => 10,
            ],
        ]);

        $this->addMockResponses([
            ['status' => 200, 'body' => $mixedToolResponse],
            ['status' => 200, 'body' => $finalResponse],
        ]);

        $runner = new ToolRunner(
            $this->testClient,
            [
                'get_weather' => fn ($args) => "Weather in {$args['location']}: Sunny",
            ],
        );

        $result = $runner->run([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Get weather and execute code'],
            ],
        ]);

        // Should complete successfully
        $this->assertEquals('msg_test_final', $result->id);
        $this->assertEquals('end_turn', $result->stop_reason);
    }

    public function testStreamingToolRunnerHandlesServerTools(): void
    {
        // Response with server tool
        $serverToolResponse = json_encode([
            'id' => 'msg_test_streaming_server',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'server_tool_use',
                    'id' => 'toolu_server_001',
                    'name' => 'bash_execution',
                    'input' => ['command' => 'echo hello'],
                ],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'tool_use',
            'usage' => [
                'input_tokens' => 50,
                'output_tokens' => 25,
            ],
        ]);

        $finalResponse = json_encode([
            'id' => 'msg_test_final_streaming',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                ['type' => 'text', 'text' => 'Command executed: hello'],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 75,
                'output_tokens' => 15,
            ],
        ]);

        // Create proper streaming SSE events
        $streamBody1 = $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => json_decode($serverToolResponse, true),
        ]);
        $streamBody1 .= $this->createStreamingEvent('content_block_start', [
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => [
                'type' => 'server_tool_use',
                'id' => 'toolu_server_001',
                'name' => 'bash_execution',
                'input' => new \stdClass(),
            ],
        ]);
        $streamBody1 .= $this->createStreamingEvent('content_block_stop', [
            'type' => 'content_block_stop',
            'index' => 0,
        ]);
        $streamBody1 .= $this->createStreamingEvent('message_delta', [
            'type' => 'message_delta',
            'delta' => ['stop_reason' => 'tool_use'],
            'usage' => ['output_tokens' => 25],
        ]);
        $streamBody1 .= "event: message_stop\ndata: [DONE]\n\n";

        $streamBody2 = $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => json_decode($finalResponse, true),
        ]);
        $streamBody2 .= $this->createStreamingEvent('content_block_start', [
            'type' => 'content_block_start',
            'index' => 0,
            'content_block' => ['type' => 'text', 'text' => ''],
        ]);
        $streamBody2 .= $this->createStreamingEvent('content_block_delta', [
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => ['type' => 'text_delta', 'text' => 'Command executed: hello'],
        ]);
        $streamBody2 .= $this->createStreamingEvent('content_block_stop', [
            'type' => 'content_block_stop',
            'index' => 0,
        ]);
        $streamBody2 .= $this->createStreamingEvent('message_delta', [
            'type' => 'message_delta',
            'delta' => ['stop_reason' => 'end_turn'],
            'usage' => ['output_tokens' => 15],
        ]);
        $streamBody2 .= "event: message_stop\ndata: [DONE]\n\n";

        $this->addMockResponses([
            ['status' => 200, 'headers' => ['Content-Type' => 'text/event-stream'], 'body' => $streamBody1],
            ['status' => 200, 'headers' => ['Content-Type' => 'text/event-stream'], 'body' => $streamBody2],
        ]);

        $runner = new StreamingToolRunner($this->testClient, [], 10);

        $result = $runner->run([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Run a bash command'],
            ],
        ]);

        // Should complete successfully
        $this->assertEquals('msg_test_final_streaming', $result->id);
        $this->assertEquals('end_turn', $result->stop_reason);
    }

    public function testServerToolsDoNotRequireLocalExecution(): void
    {
        // Verify that server tools don't trigger local handler lookup
        $toolResponse = json_encode([
            'id' => 'msg_test_no_handler',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'server_tool_use',
                    'id' => 'toolu_server_001',
                    'name' => 'code_execution',
                    'input' => ['code' => 'x = 42'],
                ],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'tool_use',
            'usage' => [
                'input_tokens' => 50,
                'output_tokens' => 25,
            ],
        ]);

        $finalResponse = json_encode([
            'id' => 'msg_test_done',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                ['type' => 'text', 'text' => 'Code executed successfully'],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 75,
                'output_tokens' => 10,
            ],
        ]);

        $this->addMockResponses([
            ['status' => 200, 'body' => $toolResponse],
            ['status' => 200, 'body' => $finalResponse],
        ]);

        // No handlers registered for code_execution - should still work
        $runner = new ToolRunner($this->testClient, [], 10);

        $result = $runner->run([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Execute code'],
            ],
        ]);

        // Should complete without errors even though no handler was provided
        $this->assertEquals('msg_test_done', $result->id);
    }
}
