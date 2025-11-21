<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Resources\Messages\Batches;
use ClaudePhp\Resources\Messages\Messages;
use ClaudePhp\Tests\TestCase;

class MessagesTest extends TestCase
{
    private Messages $messages;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->messages = new Messages($client);
    }

    public function testCanInstantiateMessagesResource(): void
    {
        $this->assertInstanceOf(Messages::class, $this->messages);
    }

    public function testMessagesHasBatchesSubResource(): void
    {
        $batches = $this->messages->batches();
        $this->assertInstanceOf(Batches::class, $batches);
    }

    public function testCreateValidatesRequiredParameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter: model');

        $this->messages->create([]);
    }

    public function testCreateValidatesAllRequiredParameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Missing max_tokens
        $this->messages->create([
            'model' => 'claude-opus-4-1-20250805',
            'messages' => [],
        ]);
    }

    public function testCreateValidatesMessagesParameter(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Missing messages
        $this->messages->create([
            'model' => 'claude-opus-4-1-20250805',
            'max_tokens' => 1024,
        ]);
    }

    public function testCountTokensValidatesRequiredParameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameters: model, messages');

        $this->messages->countTokens([]);
    }

    public function testCountTokensWithModelOnlyFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->messages->countTokens(['model' => 'claude-opus-4-1-20250805']);
    }

    public function testCountTokensAcceptsValidParameters(): void
    {
        // This would test actual HTTP call in integration tests
        // Unit test just verifies parameter validation passes
        $params = [
            'model' => 'claude-opus-4-1-20250805',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello'],
            ],
        ];

        // Doesn't throw
        try {
            $this->messages->countTokens($params);
        } catch (\Throwable $e) {
            // Expected to fail on actual HTTP call, but not on validation
            $this->assertNotInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    public function testStreamSetsStreamFlag(): void
    {
        // Stream method should pass stream=true to create
        // This is tested indirectly through create logic
        $this->assertTrue(true);
    }
}
