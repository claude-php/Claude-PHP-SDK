<?php

declare(strict_types=1);

namespace Tests\Unit\Lib\Foundry;

use ClaudePhp\Lib\Foundry\AnthropicFoundry;
use PHPUnit\Framework\TestCase;

class AnthropicFoundryTest extends TestCase
{
    public function testConstructorWithApiKey(): void
    {
        $client = new AnthropicFoundry(
            resource: 'test-resource',
            apiKey: 'test-key',
        );

        $this->assertInstanceOf(AnthropicFoundry::class, $client);
        $this->assertEquals('test-resource', $client->getResource());
    }

    public function testConstructorWithTokenProvider(): void
    {
        $client = new AnthropicFoundry(
            resource: 'test-resource',
            azureAdTokenProvider: fn () => 'test-token',
        );

        $this->assertInstanceOf(AnthropicFoundry::class, $client);
        $this->assertEquals('test-resource', $client->getResource());
    }

    public function testConstructorRequiresAuthentication(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Either apiKey or azureAdTokenProvider must be provided');

        new AnthropicFoundry(
            resource: 'test-resource',
        );
    }

    public function testGetters(): void
    {
        $client = new AnthropicFoundry(
            resource: 'test-resource',
            apiKey: 'test-key',
            timeout: 45.0,
        );

        $this->assertEquals('test-resource', $client->getResource());
        $this->assertEquals(45.0, $client->getTimeout());
    }

    public function testCustomTimeout(): void
    {
        $client = new AnthropicFoundry(
            resource: 'test-resource',
            apiKey: 'test-key',
            timeout: 60.0,
        );

        $this->assertEquals(60.0, $client->getTimeout());
    }

    public function testCustomHeaders(): void
    {
        $client = new AnthropicFoundry(
            resource: 'test-resource',
            apiKey: 'test-key',
            customHeaders: [
                'X-Custom-Header' => 'custom-value',
            ],
        );

        $this->assertInstanceOf(AnthropicFoundry::class, $client);
    }
}
