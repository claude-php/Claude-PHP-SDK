<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Vertex;

use ClaudePhp\Lib\Vertex\AnthropicVertex;
use PHPUnit\Framework\TestCase;

class VertexRegionTest extends TestCase
{
    public function testUsMultiRegionUrl(): void
    {
        $vertex = new AnthropicVertex('project-123', 'us');
        $reflection = new \ReflectionMethod($vertex, 'regionBaseUrl');

        $url = $reflection->invoke(null, 'us');
        $this->assertSame('https://aiplatform.us.rep.googleapis.com/v1', $url);
    }

    public function testEuMultiRegionUrl(): void
    {
        $reflection = new \ReflectionMethod(AnthropicVertex::class, 'regionBaseUrl');

        $url = $reflection->invoke(null, 'eu');
        $this->assertSame('https://aiplatform.eu.rep.googleapis.com/v1', $url);
    }

    public function testGlobalRegionUrl(): void
    {
        $reflection = new \ReflectionMethod(AnthropicVertex::class, 'regionBaseUrl');

        $url = $reflection->invoke(null, 'global');
        $this->assertSame('https://aiplatform.googleapis.com/v1', $url);
    }

    public function testStandardRegionUrl(): void
    {
        $reflection = new \ReflectionMethod(AnthropicVertex::class, 'regionBaseUrl');

        $url = $reflection->invoke(null, 'us-central1');
        $this->assertSame('https://us-central1-aiplatform.googleapis.com/v1beta1', $url);
    }
}
