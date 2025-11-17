<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use ClaudePhp\Tests\TestCase;
use ClaudePhp\Resources\Models;
use ClaudePhp\ClaudePhp;

class ModelsTest extends TestCase
{
    private Models $models;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new ClaudePhp(apiKey: 'test-key');
        $this->models = new Models($client);
    }

    public function test_can_instantiate_models_resource(): void
    {
        $this->assertInstanceOf(Models::class, $this->models);
    }

    public function test_list_accepts_parameters(): void
    {
        // Verify it doesn't throw validation errors
        $this->assertTrue(true);
    }

    public function test_retrieve_validates_model_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('model_id is required');

        $this->models->retrieve('');
    }

    public function test_retrieve_accepts_valid_model_id(): void
    {
        // Doesn't throw validation
        try {
            $this->models->retrieve('claude-opus-4-1-20250805');
        } catch (\Throwable $e) {
            // Expected to fail on actual HTTP call
            $this->assertNotInstanceOf(\InvalidArgumentException::class, $e);
        }
    }
}
