<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use function Amp\async;

use ClaudePhp\Utils\AsyncUtils;
use PHPUnit\Framework\TestCase;

class AsyncUtilsTest extends TestCase
{
    public function testGetAsyncLibraryReturnsFalseWhenLoopIdle(): void
    {
        $this->assertSame('false', AsyncUtils::getAsyncLibrary());
    }

    public function testGetAsyncLibraryDetectsAmpLoop(): void
    {
        $future = async(static fn () => AsyncUtils::getAsyncLibrary());
        $this->assertSame('amphp', $future->await());
    }

    public function testAsyncifyWrapsCallable(): void
    {
        $callable = static fn (int $value): int => $value * 2;
        $wrapped = AsyncUtils::asyncify($callable);

        $this->assertSame(8, $wrapped(4)->await());
    }

    public function testAwaitAllResolvesFutures(): void
    {
        $futures = [
            'a' => async(static fn () => 1),
            'b' => async(static fn () => 2),
        ];

        $this->assertSame(['a' => 1, 'b' => 2], AsyncUtils::awaitAll($futures));
    }
}
