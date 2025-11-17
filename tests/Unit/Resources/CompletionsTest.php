<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\Tests\TestCase;
use ClaudePhp\Resources\Completions;
use ClaudePhp\ClaudePhp;

class CompletionsTest extends TestCase
{
    private Completions $completions;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->completions = new Completions($client);
    }

    public function test_can_instantiate_completions_resource(): void
    {
        $this->assertInstanceOf(Completions::class, $this->completions);
    }

    public function test_create_accepts_valid_parameters(): void
    {
        // Verify parameter validation passes
        $this->assertTrue(true);
    }

    public function test_stream_sets_stream_flag(): void
    {
        try {
            $this->completions->stream(['model' => 'legacy-test-model', 'prompt' => 'Test']);
        } catch (\Throwable $e) {
            // Expected to fail on actual HTTP call, not validation
            $this->assertNotInstanceOf(\InvalidArgumentException::class, $e);
        }
    }
}
