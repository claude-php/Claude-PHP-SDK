<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Client\HttpClient;
use ClaudePhp\Resources\Beta\Messages;
use ClaudePhp\Responses\Message;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class BetaMessagesTest extends TestCase
{
    private function createMockClient(callable $responseCallback): ClaudePhp
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        $mockRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $mockStreamFactory = $this->createMock(StreamFactoryInterface::class);

        $mockRequest = $this->createMock(RequestInterface::class);
        $mockRequest->method('withHeader')->willReturnSelf();
        $mockRequest->method('withBody')->willReturnSelf();

        $mockRequestFactory->method('createRequest')->willReturn($mockRequest);

        $mockStream = $this->createMock(StreamInterface::class);
        $mockStreamFactory->method('createStream')->willReturn($mockStream);

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $responseCallback($mockResponse, $mockRequest);

        $mockHttpClient->method('sendRequest')->willReturn($mockResponse);

        return new ClaudePhp(
            apiKey: 'test-api-key',
            httpClient: $mockHttpClient,
            requestFactory: $mockRequestFactory,
            streamFactory: $mockStreamFactory
        );
    }

    public function testBetaHeaderIsSetWhenBetasProvided(): void
    {
        $headersSent = [];

        $client = $this->createMockClient(function ($mockResponse, $mockRequest) use (&$headersSent) {
            $mockRequest->method('withHeader')
                ->willReturnCallback(function ($name, $value) use ($mockRequest, &$headersSent) {
                    $headersSent[$name] = $value;
                    return $mockRequest;
                });

            $mockResponse->method('getBody')->willReturn($this->createMock(StreamInterface::class));
            $mockResponse->getBody()->method('__toString')->willReturn(json_encode([
                'id' => 'msg_123',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'text', 'text' => 'Hello!']],
                'model' => 'claude-sonnet-4-5',
                'stop_reason' => 'end_turn',
                'usage' => [
                    'input_tokens' => 10,
                    'output_tokens' => 20,
                ],
            ]));
        });

        $result = $client->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
            'betas' => ['test-feature-2024-01-01', 'another-feature-2024-02-01'],
        ]);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertArrayHasKey('anthropic-beta', $headersSent);
        $this->assertEquals('test-feature-2024-01-01,another-feature-2024-02-01', $headersSent['anthropic-beta']);
    }

    public function testBetaHeaderNotSetWhenNoBetas(): void
    {
        $headersSent = [];

        $client = $this->createMockClient(function ($mockResponse, $mockRequest) use (&$headersSent) {
            $mockRequest->method('withHeader')
                ->willReturnCallback(function ($name, $value) use ($mockRequest, &$headersSent) {
                    $headersSent[$name] = $value;
                    return $mockRequest;
                });

            $mockResponse->method('getBody')->willReturn($this->createMock(StreamInterface::class));
            $mockResponse->getBody()->method('__toString')->willReturn(json_encode([
                'id' => 'msg_123',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'text', 'text' => 'Hello!']],
                'model' => 'claude-sonnet-4-5',
                'stop_reason' => 'end_turn',
                'usage' => [
                    'input_tokens' => 10,
                    'output_tokens' => 20,
                ],
            ]));
        });

        $result = $client->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
        ]);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertArrayNotHasKey('anthropic-beta', $headersSent);
    }

    public function testSingleBetaFeature(): void
    {
        $headersSent = [];

        $client = $this->createMockClient(function ($mockResponse, $mockRequest) use (&$headersSent) {
            $mockRequest->method('withHeader')
                ->willReturnCallback(function ($name, $value) use ($mockRequest, &$headersSent) {
                    $headersSent[$name] = $value;
                    return $mockRequest;
                });

            $mockResponse->method('getBody')->willReturn($this->createMock(StreamInterface::class));
            $mockResponse->getBody()->method('__toString')->willReturn(json_encode([
                'id' => 'msg_123',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'text', 'text' => 'Hello!']],
                'model' => 'claude-sonnet-4-5',
                'stop_reason' => 'end_turn',
                'usage' => [
                    'input_tokens' => 10,
                    'output_tokens' => 20,
                ],
            ]));
        });

        $result = $client->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
            'betas' => ['structured-outputs-2025-09-17'],
        ]);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertArrayHasKey('anthropic-beta', $headersSent);
        $this->assertEquals('structured-outputs-2025-09-17', $headersSent['anthropic-beta']);
    }

    public function testBetasNotInRequestBody(): void
    {
        $requestBodySent = null;

        $client = $this->createMockClient(function ($mockResponse, $mockRequest) use (&$requestBodySent) {
            $mockRequest->method('withBody')
                ->willReturnCallback(function ($body) use ($mockRequest, &$requestBodySent) {
                    $requestBodySent = (string) $body;
                    return $mockRequest;
                });

            $mockResponse->method('getBody')->willReturn($this->createMock(StreamInterface::class));
            $mockResponse->getBody()->method('__toString')->willReturn(json_encode([
                'id' => 'msg_123',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'text', 'text' => 'Hello!']],
                'model' => 'claude-sonnet-4-5',
                'stop_reason' => 'end_turn',
                'usage' => [
                    'input_tokens' => 10,
                    'output_tokens' => 20,
                ],
            ]));
        });

        $client->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
            'betas' => ['test-feature-2024-01-01'],
        ]);

        $this->assertNotNull($requestBodySent);
        $bodyArray = json_decode($requestBodySent, true);
        $this->assertIsArray($bodyArray);
        $this->assertArrayNotHasKey('betas', $bodyArray, 'betas should not be in request body');
    }

    public function testEmptyBetasArrayDoesNotSetHeader(): void
    {
        $headersSent = [];

        $client = $this->createMockClient(function ($mockResponse, $mockRequest) use (&$headersSent) {
            $mockRequest->method('withHeader')
                ->willReturnCallback(function ($name, $value) use ($mockRequest, &$headersSent) {
                    $headersSent[$name] = $value;
                    return $mockRequest;
                });

            $mockResponse->method('getBody')->willReturn($this->createMock(StreamInterface::class));
            $mockResponse->getBody()->method('__toString')->willReturn(json_encode([
                'id' => 'msg_123',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [['type' => 'text', 'text' => 'Hello!']],
                'model' => 'claude-sonnet-4-5',
                'stop_reason' => 'end_turn',
                'usage' => [
                    'input_tokens' => 10,
                    'output_tokens' => 20,
                ],
            ]));
        });

        $result = $client->beta()->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!'],
            ],
            'betas' => [],
        ]);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertArrayNotHasKey('anthropic-beta', $headersSent);
    }
}
