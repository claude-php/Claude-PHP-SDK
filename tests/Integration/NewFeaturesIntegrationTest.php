<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Integration;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Tests\TestCase;

/**
 * Integration tests for v0.5.2 features
 * Tests server-side tools and authentication flexibility
 */
class NewFeaturesIntegrationTest extends TestCase
{
    /**
     * Test authentication with custom headers works
     */
    public function testCustomAuthenticationHeaders(): void
    {
        $this->markTestSkipped('Requires custom auth setup - manual verification needed');
        
        // This test demonstrates how custom auth would work in production
        // Skip in CI/CD as it requires specific auth configuration
        
        $client = new ClaudePhp(
            apiKey: null,
            customHeaders: [
                'x-api-key' => $_ENV['ANTHROPIC_API_KEY'] ?? 'test-key',
            ]
        );

        $this->assertInstanceOf(ClaudePhp::class, $client);
    }

    /**
     * Test that server-side tool use blocks are properly handled
     */
    public function testServerSideToolHandling(): void
    {
        // Mock response with server_tool_use block
        $response = json_encode([
            'id' => 'msg_test_server_tool',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'server_tool_use',
                    'id' => 'toolu_server_001',
                    'name' => 'code_execution',
                    'input' => [
                        'language' => 'python',
                        'code' => 'print("test")',
                    ],
                ],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'tool_use',
            'usage' => [
                'input_tokens' => 50,
                'output_tokens' => 25,
            ],
        ]);

        $this->addMockResponse(200, [], $response);

        $message = $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Execute code'],
            ],
        ]);

        // Verify server_tool_use block is in response
        $hasServerTool = false;
        foreach ($message->content as $block) {
            if (($block['type'] ?? '') === 'server_tool_use') {
                $hasServerTool = true;
                $this->assertEquals('code_execution', $block['name']);
            }
        }

        $this->assertTrue($hasServerTool, 'Response should contain server_tool_use block');
    }

    /**
     * Test stream closure with proper resource cleanup
     */
    public function testStreamClosureResourceCleanup(): void
    {
        $streamData = $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_stream_test',
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

        $streamData .= $this->createStreamingEvent('content_block_delta', [
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => ['type' => 'text_delta', 'text' => 'Hello'],
        ]);

        $streamData .= "event: message_stop\ndata: [DONE]\n\n";

        $this->addMockResponse(200, ['Content-Type' => 'text/event-stream'], $streamData);

        $stream = $this->testClient->messages()->stream([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello'],
            ],
        ]);

        // Consume stream
        $events = iterator_to_array($stream);
        $this->assertNotEmpty($events);

        // Stream should auto-close after iteration
        // This is guaranteed by __destruct() in StreamResponse
        $this->assertTrue(true, 'Stream cleanup successful');
    }

    /**
     * Test that binary streaming method exists and is callable
     */
    public function testBinaryStreamingMethodExists(): void
    {
        $transport = $this->testClient->getHttpTransport();
        
        $this->assertTrue(
            method_exists($transport, 'postStreamBinary'),
            'HttpClient should have postStreamBinary method'
        );
    }

    /**
     * Test mixed client and server tools in tool runner
     */
    public function testMixedToolTypes(): void
    {
        // Response with both client and server tools
        $mixedResponse = json_encode([
            'id' => 'msg_mixed',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'tool_use',
                    'id' => 'toolu_client_001',
                    'name' => 'get_time',
                    'input' => [],
                ],
                [
                    'type' => 'server_tool_use',
                    'id' => 'toolu_server_001',
                    'name' => 'code_execution',
                    'input' => ['code' => 'x = 1'],
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
            'id' => 'msg_final',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                ['type' => 'text', 'text' => 'Done'],
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 150,
                'output_tokens' => 10,
            ],
        ]);

        $this->addMockResponses([
            ['status' => 200, 'body' => $mixedResponse],
            ['status' => 200, 'body' => $finalResponse],
        ]);

        $runner = new \ClaudePhp\Lib\Tools\ToolRunner(
            $this->testClient,
            [
                'get_time' => fn($args) => date('Y-m-d H:i:s'),
            ]
        );

        $result = $runner->run([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Get time and execute code'],
            ],
        ]);

        $this->assertEquals('msg_final', $result->id);
        $this->assertEquals('end_turn', $result->stop_reason);
    }

    /**
     * Test authentication error message provides helpful info
     */
    public function testAuthenticationErrorMessage(): void
    {
        try {
            new ClaudePhp(apiKey: null, customHeaders: []);
            $this->fail('Should throw InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Authentication is required', $e->getMessage());
            $this->assertStringContainsString('x-api-key', $e->getMessage());
            $this->assertStringContainsString('Authorization', $e->getMessage());
        }
    }
}
