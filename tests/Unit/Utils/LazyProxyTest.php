<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Tests\TestCase;
use ClaudePhp\Utils\LazyProxy;

class LazyProxyTest extends TestCase
{
    public function testLazyProxyDefersInstantiation(): void
    {
        $proxy = new class extends LazyProxy {
            public int $loadCount = 0;

            protected function load(): object
            {
                $this->loadCount++;
                return new class {
                    public string $name = 'proxy';

                    public function greet(): string
                    {
                        return 'hello';
                    }
                };
            }

            public function getLoadCount(): int
            {
                return $this->loadCount;
            }
        };

        // No loading until first access
        $this->assertSame(0, $proxy->getLoadCount());
        $this->assertSame('hello', $proxy->greet());
        $this->assertSame('proxy', $proxy->name);
        $this->assertSame(1, $proxy->getLoadCount());
    }
}
