<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit;

use ClaudePhp\ClaudePhp;
use ClaudePhp\ClaudePhpAsyncProxy;
use ClaudePhp\Resources\AsyncResourceProxy;
use ClaudePhp\Tests\TestCase;

class ClaudePhpAsyncProxyTest extends TestCase
{
    public function testAsyncReturnsProxy(): void
    {
        $client = new ClaudePhp(apiKey: 'key');
        $this->assertInstanceOf(ClaudePhpAsyncProxy::class, $client->async());
    }

    public function testAsyncMessagesReturnsAsyncResourceProxy(): void
    {
        $client = new ClaudePhp(apiKey: 'key');
        $proxy = $client->async();

        $this->assertInstanceOf(AsyncResourceProxy::class, $proxy->messages());
    }

    public function testAsyncProxyForwardsNonResourceMethods(): void
    {
        $client = new ClaudePhp(apiKey: 'key');
        $proxy = $client->async();

        $this->assertSame($client->getBaseUrl(), $proxy->getBaseUrl());
    }
}
