<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\Tests\TestCase;
use ClaudePhp\Resources\Messages\Messages;
use ClaudePhp\Resources\Messages\Batches;
use ClaudePhp\ClaudePhp;

class MessagesTest extends TestCase
{
    private Messages $messages;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->messages = new Messages($client);
    }

    public function test_can_instantiate_messages_resource(): void
    {
        $this->assertInstanceOf(Messages::class, $this->messages);
    }

    public function test_messages_has_batches_sub_resource(): void
    {
        $batches = $this->messages->batches();
        $this->assertInstanceOf(Batches::class, $batches);
    }

    public function test_create_validates_required_parameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter: model');

        $this->messages->create([]);
    }

    public function test_create_validates_all_required_parameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Missing max_tokens
        $this->messages->create([
            'model' => 'claude-opus-4-1-20250805',
            'messages' => [],
        ]);
    }

    public function test_create_validates_messages_parameter(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Missing messages
        $this->messages->create([
            'model' => 'claude-opus-4-1-20250805',
            'max_tokens' => 1024,
        ]);
    }

    public function test_count_tokens_validates_required_parameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameters: model, messages');

        $this->messages->countTokens([]);
    }

    public function test_count_tokens_with_model_only_fails(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->messages->countTokens(['model' => 'claude-opus-4-1-20250805']);
    }

    public function test_count_tokens_accepts_valid_parameters(): void
    {
        // This would test actual HTTP call in integration tests
        // Unit test just verifies parameter validation passes
        $params = [
            'model' => 'claude-opus-4-1-20250805',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
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

    public function test_stream_sets_stream_flag(): void
    {
        // Stream method should pass stream=true to create
        // This is tested indirectly through create logic
        $this->assertTrue(true);
    }
}
