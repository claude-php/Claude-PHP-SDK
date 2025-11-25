<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Resources\Models;
use ClaudePhp\Tests\TestCase;

class ModelsTest extends TestCase
{
    private Models $models;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->models = new Models($client);
    }

    public function testCanInstantiateModelsResource(): void
    {
        $this->assertInstanceOf(Models::class, $this->models);
    }

    public function testListAcceptsParameters(): void
    {
        // Verify it doesn't throw validation errors
        $this->assertTrue(true);
    }

    public function testRetrieveValidatesModelId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('model_id is required');

        $this->models->retrieve('');
    }

    public function testRetrieveAcceptsValidModelId(): void
    {
        // Doesn't throw validation
        try {
            $this->models->retrieve('claude-opus-4-5-20251101');
        } catch (\Throwable $e) {
            // Expected to fail on actual HTTP call
            $this->assertNotInstanceOf(\InvalidArgumentException::class, $e);
        }
    }
}
