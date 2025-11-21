<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Resources;

use Amp\Future;
use ClaudePhp\ClaudePhp;
use ClaudePhp\Resources\AsyncResourceProxy;
use ClaudePhp\Resources\Resource;
use ClaudePhp\Tests\TestCase;

class ResourceAsyncTest extends TestCase
{
    public function testAsyncReturnsProxyAndWrapsMethods(): void
    {
        $client = new ClaudePhp(apiKey: 'test-key');
        $resource = new class ($client) extends Resource {
            public function ping(): string
            {
                return 'pong';
            }
        };

        $proxy = $resource->async();
        $this->assertInstanceOf(AsyncResourceProxy::class, $proxy);

        $future = $proxy->ping();
        $this->assertInstanceOf(Future::class, $future);
        $this->assertSame('pong', $future->await());
    }
}
