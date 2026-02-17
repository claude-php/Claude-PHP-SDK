<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Integration;

use ClaudePhp\Responses\StreamResponse;
use ClaudePhp\Tests\TestCase;
use ClaudePhp\Types\Message;
use ClaudePhp\Types\MessageTokensCount;

/**
 * Integration tests for Messages API
 * Equivalent to Python SDK's comprehensive API testing
 */
class MessagesApiTest extends TestCase
{
    public function testCreateMessage(): void
    {
        // Setup mock response
        $responseBody = $this->createMessageResponse('Hello, how can I help you?');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        // Make request
        $response = $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        // Assertions
        $this->assertInstanceOf(Message::class, $response);
        $this->assertEquals('assistant', $response->role);
        $this->assertIsArray($response->content);
        $this->assertNotEmpty($response->content);

        // Verify HTTP request
        $this->assertHttpRequestMade('POST', '/messages', [
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        // Verify headers
        $this->assertHttpHeadersPresent([
            'Content-Type' => 'application/json',
            'User-Agent' => 'ClaudePhp/0.6.0',
            'anthropic-version' => '2023-06-01',
        ]);
    }

    public function testCreateMessageWithSystemPrompt(): void
    {
        $responseBody = $this->createMessageResponse('I am a helpful assistant.');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $response = $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'system' => 'You are a helpful assistant.',
            'messages' => [
                ['role' => 'user', 'content' => 'Who are you?'],
            ],
        ]);

        $this->assertInstanceOf(Message::class, $response);
        $this->assertHttpRequestMade('POST', '/messages');

        $lastRequest = $this->getLastRequest();
        $body = json_decode((string) $lastRequest->getBody(), true);
        $this->assertEquals('You are a helpful assistant.', $body['system']);
    }

    public function testCreateMessageWithTools(): void
    {
        $responseBody = $this->createMessageResponse('I can help with that calculation.');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $tools = [
            [
                'name' => 'calculator',
                'description' => 'A simple calculator',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'operation' => ['type' => 'string'],
                        'numbers' => ['type' => 'array', 'items' => ['type' => 'number']],
                    ],
                    'required' => ['operation', 'numbers'],
                ],
            ],
        ];

        $response = $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Calculate 2 + 2'],
            ],
            'tools' => $tools,
        ]);

        $this->assertInstanceOf(Message::class, $response);

        $lastRequest = $this->getLastRequest();
        $body = json_decode((string) $lastRequest->getBody(), true);
        $this->assertEquals($tools, $body['tools']);
    }

    public function testCreateMessageWithMetadata(): void
    {
        $responseBody = $this->createMessageResponse('Response with metadata');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $metadata = [
            'user_id' => 'user123',
            'conversation_id' => 'conv456',
        ];

        $response = $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello with metadata'],
            ],
            'metadata' => $metadata,
        ]);

        $this->assertInstanceOf(Message::class, $response);

        $lastRequest = $this->getLastRequest();
        $body = json_decode((string) $lastRequest->getBody(), true);
        $this->assertEquals($metadata, $body['metadata']);
    }

    public function testStreamMessage(): void
    {
        // Create streaming response
        $streamData = $this->createStreamingEvent('message_start', [
            'type' => 'message_start',
            'message' => [
                'id' => 'msg_123',
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

        $streamData .= $this->createStreamingEvent('content_block_delta', [
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => ['type' => 'text_delta', 'text' => 'Hello'],
        ]);

        $streamData .= 'event: message_stop\ndata: [DONE]\n\n';

        $this->addMockResponse(200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ], $streamData);

        $response = $this->testClient->messages()->stream([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        $this->assertInstanceOf(StreamResponse::class, $response);
        $this->assertHttpRequestMade('POST', '/messages');

        $lastRequest = $this->getLastRequest();
        $body = json_decode((string) $lastRequest->getBody(), true);
        $this->assertTrue($body['stream']);
    }

    public function testCountTokens(): void
    {
        $responseBody = json_encode([
            'input_tokens' => 15,
            'output_tokens' => 0,
        ]);

        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $response = $this->testClient->messages()->countTokens([
            'model' => 'claude-sonnet-4-5-20250929',
            'messages' => [
                ['role' => 'user', 'content' => 'Count my tokens please'],
            ],
        ]);

        $this->assertInstanceOf(MessageTokensCount::class, $response);
        $this->assertEquals(15, $response->input_tokens);
        $this->assertEquals(0, $response->output_tokens);

        $this->assertHttpRequestMade('POST', '/messages/count_tokens');
    }

    public function testApiErrorHandling(): void
    {
        $errorResponse = $this->createErrorResponse(
            'Invalid model specified',
            'invalid_request_error',
            400,
        );

        $this->addMockResponse(400, ['Content-Type' => 'application/json'], $errorResponse);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid model specified');

        $this->testClient->messages()->create([
            'model' => 'invalid-model',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);
    }

    public function testRateLimitErrorHandling(): void
    {
        $errorResponse = $this->createErrorResponse(
            'Rate limit exceeded',
            'rate_limit_error',
            429,
        );

        $this->addMockResponse(429, [
            'Content-Type' => 'application/json',
            'retry-after' => '60',
        ], $errorResponse);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);
    }

    public function testAuthenticationErrorHandling(): void
    {
        $errorResponse = $this->createErrorResponse(
            'Invalid API key',
            'authentication_error',
            401,
        );

        $this->addMockResponse(401, ['Content-Type' => 'application/json'], $errorResponse);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid API key');

        $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);
    }

    public function testRequiredParameterValidation(): void
    {
        // Test missing model
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter: model');

        $this->testClient->messages()->create([
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);
    }

    public function testMaxTokensValidation(): void
    {
        // Test missing max_tokens
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter: max_tokens');

        $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);
    }

    public function testMessagesValidation(): void
    {
        // Test missing messages
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter: messages');

        $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
        ]);
    }

    public function testTemperatureParameter(): void
    {
        $responseBody = $this->createMessageResponse('Temperature test response');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'temperature' => 0.7,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        $lastRequest = $this->getLastRequest();
        $body = json_decode((string) $lastRequest->getBody(), true);
        $this->assertEquals(0.7, $body['temperature']);
    }

    public function testTopPAndTopKParameters(): void
    {
        $responseBody = $this->createMessageResponse('Sampling test response');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'top_p' => 0.9,
            'top_k' => 50,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        $lastRequest = $this->getLastRequest();
        $body = json_decode((string) $lastRequest->getBody(), true);
        $this->assertEquals(0.9, $body['top_p']);
        $this->assertEquals(50, $body['top_k']);
    }

    public function testStopSequences(): void
    {
        $responseBody = $this->createMessageResponse('Response with stop sequences');
        $this->addMockResponse(200, ['Content-Type' => 'application/json'], $responseBody);

        $stopSequences = ['\n\n', 'END'];

        $this->testClient->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 100,
            'stop_sequences' => $stopSequences,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        $lastRequest = $this->getLastRequest();
        $body = json_decode((string) $lastRequest->getBody(), true);
        $this->assertEquals($stopSequences, $body['stop_sequences']);
    }
}
