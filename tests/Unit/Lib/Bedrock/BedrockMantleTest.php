<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Bedrock;

use ClaudePhp\Lib\Bedrock\AnthropicBedrockMantle;
use PHPUnit\Framework\TestCase;

class BedrockMantleTest extends TestCase
{
    public function testDefaultBaseUrl(): void
    {
        $mantle = new AnthropicBedrockMantle(apiKey: 'test-key', region: 'us-west-2');
        $this->assertSame('https://bedrock-mantle.us-west-2.api.aws/anthropic', $mantle->getBaseUrl());
    }

    public function testCustomBaseUrl(): void
    {
        $mantle = new AnthropicBedrockMantle(
            apiKey: 'test-key',
            baseUrl: 'https://custom.mantle.example.com',
        );
        $this->assertSame('https://custom.mantle.example.com', $mantle->getBaseUrl());
    }

    public function testBearerAuthHeaders(): void
    {
        $mantle = new AnthropicBedrockMantle(apiKey: 'my-secret-token', region: 'us-east-1');
        $headers = $mantle->authHeaders();
        $this->assertSame('Bearer my-secret-token', $headers['Authorization']);
    }

    public function testSkipAuthReturnsEmptyHeaders(): void
    {
        $mantle = new AnthropicBedrockMantle(apiKey: 'key', skipAuth: true);
        $this->assertEmpty($mantle->authHeaders());
    }

    public function testRegionAccessor(): void
    {
        $mantle = new AnthropicBedrockMantle(apiKey: 'key', region: 'eu-west-1');
        $this->assertSame('eu-west-1', $mantle->getRegion());
    }

    public function testMutualExclusionThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AnthropicBedrockMantle(apiKey: 'key', awsAccessKey: 'AK');
    }

    public function testBetaAccessor(): void
    {
        $mantle = new AnthropicBedrockMantle(apiKey: 'key');
        $beta = $mantle->beta();
        $this->assertNotNull($beta);
        $messages = $beta->messages();
        $this->assertNotNull($messages);
    }
}
