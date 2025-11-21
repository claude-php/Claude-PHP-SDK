<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use function Amp\async;

use Amp\Future;
use ClaudePhp\Utils\Streams;
use PHPUnit\Framework\TestCase;

class StreamsTest extends TestCase
{
    public function testConsumeSyncIterator(): void
    {
        $iterator = new class implements \IteratorAggregate {
            public function getIterator(): \Iterator
            {
                yield 1;

                yield 2;

                yield 3;
            }
        };

        // Should not throw and consume all items
        Streams::consumeSyncIterator($iterator->getIterator());
        $this->assertTrue(true);
    }

    public function testCollectSyncIterator(): void
    {
        $iterator = new class implements \IteratorAggregate {
            public function getIterator(): \Iterator
            {
                yield 1;

                yield 2;

                yield 3;
            }
        };

        $result = Streams::collectSyncIterator($iterator->getIterator());

        $this->assertSame([1, 2, 3], $result);
    }

    public function testMapSyncIterator(): void
    {
        $iterator = [1, 2, 3];
        $callback = static fn ($n) => $n * 2;

        $result = Streams::mapSyncIterator($iterator, $callback);
        $collected = iterator_to_array($result);

        $this->assertSame([2, 4, 6], $collected);
    }

    public function testFilterSyncIterator(): void
    {
        $iterator = [1, 2, 3, 4, 5];
        $predicate = static fn ($n) => $n > 2;

        $result = Streams::filterSyncIterator($iterator, $predicate);
        $collected = iterator_to_array($result);

        $this->assertSame([3, 4, 5], array_values($collected));
    }

    public function testTakeSyncIterator(): void
    {
        $iterator = [1, 2, 3, 4, 5];

        $result = Streams::takeSyncIterator($iterator, 3);
        $collected = iterator_to_array($result);

        $this->assertCount(3, $collected);
        $this->assertSame([1, 2, 3], array_values($collected));
    }

    public function testTakeSyncIteratorExceedingCount(): void
    {
        $iterator = [1, 2, 3];

        $result = Streams::takeSyncIterator($iterator, 10);
        $collected = iterator_to_array($result);

        $this->assertCount(3, $collected);
        $this->assertSame([1, 2, 3], array_values($collected));
    }

    public function testSkipSyncIterator(): void
    {
        $iterator = [1, 2, 3, 4, 5];

        $result = Streams::skipSyncIterator($iterator, 2);
        $collected = iterator_to_array($result);

        $this->assertCount(3, $collected);
        $this->assertSame([3, 4, 5], array_values($collected));
    }

    public function testSkipSyncIteratorExceedingCount(): void
    {
        $iterator = [1, 2, 3];

        $result = Streams::skipSyncIterator($iterator, 10);
        $collected = iterator_to_array($result);

        $this->assertCount(0, $collected);
    }

    public function testChainedOperations(): void
    {
        $iterator = [1, 2, 3, 4, 5];

        // Skip 1, take 3, filter even
        $result = Streams::skipSyncIterator($iterator, 1);
        $result = Streams::takeSyncIterator($result, 3);
        $result = Streams::filterSyncIterator($result, static fn ($n) => 0 === $n % 2);

        $collected = iterator_to_array($result);

        $this->assertSame([2, 4], array_values($collected));
    }

    public function testConsumeAsyncIteratorResolvesAmpFutures(): void
    {
        $futures = [
            async(static fn () => 1),
            async(static fn () => 2),
        ];

        $result = Streams::consumeAsyncIterator($futures);

        $this->assertInstanceOf(Future::class, $result);
        $result->await();
        $this->assertTrue(true);
    }

    public function testCollectAsyncIteratorReturnsResolvedValues(): void
    {
        $futures = [
            'first' => async(static fn () => 10),
            'second' => static fn () => async(static fn () => 20),
            'immediate' => 30,
        ];

        $result = Streams::collectAsyncIterator($futures);

        $this->assertInstanceOf(Future::class, $result);
        $this->assertSame(['first' => 10, 'second' => 20, 'immediate' => 30], $result->await());
    }
}
