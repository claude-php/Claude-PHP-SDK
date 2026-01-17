<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Tests for authentication flexibility improvements
 *
 * Based on Python SDK v0.76.0 feature: loosen auth header validation (5a0b89b)
 */
class AuthenticationFlexibilityTest extends TestCase
{
    public function testApiKeyAuthenticationStillWorks(): void
    {
        $client = new ClaudePhp(apiKey: 'sk-ant-test-key-123');

        $this->assertEquals('sk-ant-test-key-123', $client->getApiKey());
    }

    public function testEnvironmentVariableAuthenticationWorks(): void
    {
        $_ENV['ANTHROPIC_API_KEY'] = 'sk-ant-env-test-key';

        $client = new ClaudePhp();

        $this->assertEquals('sk-ant-env-test-key', $client->getApiKey());

        unset($_ENV['ANTHROPIC_API_KEY']);
    }

    public function testCustomXApiKeyHeaderAllowed(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->createMessageResponse()),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $history = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($history));
        $httpClient = new Client(['handler' => $handlerStack]);

        // Create client without API key but with custom x-api-key header
        $client = new ClaudePhp(
            apiKey: null,
            customHeaders: ['x-api-key' => 'sk-ant-custom-header-key'],
            httpClient: $httpClient,
        );

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello'],
            ],
        ]);

        $this->assertNotNull($response);

        // Verify the custom header was used
        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertEquals('sk-ant-custom-header-key', $request->getHeaderLine('x-api-key'));
    }

    public function testCustomAuthorizationHeaderAllowed(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->createMessageResponse()),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $history = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($history));
        $httpClient = new Client(['handler' => $handlerStack]);

        // Create client with Bearer token instead of API key
        $client = new ClaudePhp(
            apiKey: null,
            customHeaders: ['Authorization' => 'Bearer custom-bearer-token'],
            httpClient: $httpClient,
        );

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello'],
            ],
        ]);

        $this->assertNotNull($response);

        // Verify the custom Authorization header was used
        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertEquals('Bearer custom-bearer-token', $request->getHeaderLine('Authorization'));
        // Should not have x-api-key header
        $this->assertFalse($request->hasHeader('x-api-key'));
    }

    public function testNoAuthenticationThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Authentication is required');

        // Try to create client without any authentication
        new ClaudePhp(apiKey: null, customHeaders: []);
    }

    public function testEmptyApiKeyWithoutCustomHeadersThrowsException(): void
    {
        // Unset environment variable
        unset($_ENV['ANTHROPIC_API_KEY']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Authentication is required');

        new ClaudePhp(apiKey: '');
    }

    public function testApiKeyNotIncludedWhenEmpty(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->createMessageResponse()),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $history = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($history));
        $httpClient = new Client(['handler' => $handlerStack]);

        // Create client with custom auth header, no API key
        $client = new ClaudePhp(
            apiKey: null,
            customHeaders: ['x-api-key' => 'custom-key'],
            httpClient: $httpClient,
        );

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello'],
            ],
        ]);

        $this->assertNotNull($response);

        // Verify headers
        $request = $history[0]['request'];
        // Should have the custom x-api-key
        $this->assertTrue($request->hasHeader('x-api-key'));
        $this->assertEquals('custom-key', $request->getHeaderLine('x-api-key'));
    }

    public function testLowercaseAuthorizationHeaderWorks(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->createMessageResponse()),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        // Test with lowercase 'authorization' header
        $client = new ClaudePhp(
            apiKey: null,
            customHeaders: ['authorization' => 'Bearer lowercase-token'],
            httpClient: $httpClient,
        );

        // Should not throw exception
        $this->assertInstanceOf(ClaudePhp::class, $client);
    }

    public function testBothApiKeyAndCustomHeadersAllowed(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], $this->createMessageResponse()),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $history = [];
        $handlerStack->push(\GuzzleHttp\Middleware::history($history));
        $httpClient = new Client(['handler' => $handlerStack]);

        // Provide both API key and custom headers
        $client = new ClaudePhp(
            apiKey: 'sk-ant-api-key',
            customHeaders: ['X-Custom-Header' => 'custom-value'],
            httpClient: $httpClient,
        );

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello'],
            ],
        ]);

        $this->assertNotNull($response);

        // Verify both headers are present
        $request = $history[0]['request'];
        $this->assertEquals('sk-ant-api-key', $request->getHeaderLine('x-api-key'));
        $this->assertEquals('custom-value', $request->getHeaderLine('X-Custom-Header'));
    }
}
