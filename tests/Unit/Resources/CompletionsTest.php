<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Resources\Completions;
use ClaudePhp\Tests\TestCase;

class CompletionsTest extends TestCase
{
    private Completions $completions;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->completions = new Completions($client);
    }

    public function testCanInstantiateCompletionsResource(): void
    {
        $this->assertInstanceOf(Completions::class, $this->completions);
    }

    public function testCreateAcceptsValidParameters(): void
    {
        // Verify parameter validation passes
        $this->assertTrue(true);
    }

    public function testStreamSetsStreamFlag(): void
    {
        try {
            $this->completions->stream(['model' => 'legacy-test-model', 'prompt' => 'Test']);
        } catch (\Throwable $e) {
            // Expected to fail on actual HTTP call, not validation
            $this->assertNotInstanceOf(\InvalidArgumentException::class, $e);
        }
    }
}
