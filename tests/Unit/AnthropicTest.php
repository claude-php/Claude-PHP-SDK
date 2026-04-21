<?php

declare(strict_types=1);

namespace ClaudePhp\Tests;

use ClaudePhp\ClaudePhp;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Tests for the main Anthropic client class
 */
class AnthropicTest extends PHPUnitTestCase
{
    /**
     * Test that client initializes with valid API key
     */
    public function testClientInitializesWithApiKey(): void
    {
        $client = new ClaudePhp(apiKey: 'test-key-12345');

        $this->assertEquals('test-key-12345', $client->getApiKey());
    }

    /**
     * Test that client throws exception when no API key provided
     */
    public function testClientThrowsExceptionWithoutApiKey(): void
    {
        // The constructor falls back to ANTHROPIC_API_KEY in $_ENV / getenv(),
        // so we must isolate the env var to assert the no-auth code path.
        $originalEnv = $_ENV['ANTHROPIC_API_KEY'] ?? null;
        $originalServer = $_SERVER['ANTHROPIC_API_KEY'] ?? null;
        $originalGetenv = getenv('ANTHROPIC_API_KEY');

        unset($_ENV['ANTHROPIC_API_KEY'], $_SERVER['ANTHROPIC_API_KEY']);
        putenv('ANTHROPIC_API_KEY');

        try {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Authentication is required');

            new ClaudePhp(apiKey: null);
        } finally {
            if (null !== $originalEnv) {
                $_ENV['ANTHROPIC_API_KEY'] = $originalEnv;
            }
            if (null !== $originalServer) {
                $_SERVER['ANTHROPIC_API_KEY'] = $originalServer;
            }
            if (false !== $originalGetenv) {
                putenv('ANTHROPIC_API_KEY=' . $originalGetenv);
            }
        }
    }

    /**
     * Test default configuration values
     */
    public function testClientHasDefaultConfiguration(): void
    {
        $client = new ClaudePhp(apiKey: 'test-key');

        $this->assertEquals(ClaudePhp::DEFAULT_BASE_URL, $client->getBaseUrl());
        $this->assertEquals(ClaudePhp::DEFAULT_TIMEOUT, $client->getTimeout());
        $this->assertEquals(ClaudePhp::DEFAULT_MAX_RETRIES, $client->getMaxRetries());
    }

    /**
     * Test custom configuration values
     */
    public function testClientAcceptsCustomConfiguration(): void
    {
        $baseUrl = 'https://custom.api.com/v1';
        $timeout = 60.0;
        $maxRetries = 5;
        $customHeaders = ['X-Custom' => 'value'];

        $client = new ClaudePhp(
            apiKey: 'test-key',
            baseUrl: $baseUrl,
            timeout: $timeout,
            maxRetries: $maxRetries,
            customHeaders: $customHeaders,
        );

        $this->assertEquals($baseUrl, $client->getBaseUrl());
        $this->assertEquals($timeout, $client->getTimeout());
        $this->assertEquals($maxRetries, $client->getMaxRetries());
        $this->assertEquals($customHeaders, $client->getCustomHeaders());
    }

    /**
     * Test that custom headers are returned correctly
     */
    public function testCustomHeadersAreReturned(): void
    {
        $headers = [
            'X-Custom-Header' => 'custom-value',
            'X-Another' => 'another-value',
        ];

        $client = new ClaudePhp(
            apiKey: 'test-key',
            customHeaders: $headers,
        );

        $this->assertEquals($headers, $client->getCustomHeaders());
    }
}
