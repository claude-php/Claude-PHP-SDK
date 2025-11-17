<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use Generator;
use Amp\Future;

use function Amp\async;

/**
 * Stream consumption utilities.
 *
 * Provides helpers for consuming synchronous and asynchronous iterators.
 */
final class Streams
{
    /**
     * Consume all items from a synchronous iterator.
     *
     * Iterates through all items in the iterator without storing them,
     * useful for side-effect operations or resource cleanup.
     *
     * @param iterable<mixed> $iterator The iterator to consume
     * @return void
     */
    public static function consumeSyncIterator(iterable $iterator): void
    {
        foreach ($iterator as $_) {
            // Consume but don't use
        }
    }

    /**
     * Collect all items from a synchronous iterator into an array.
     *
     * @template T
     * @param iterable<T> $iterator The iterator to collect from
     * @return T[] Array of all items
     */
    public static function collectSyncIterator(iterable $iterator): array
    {
        $items = [];
        foreach ($iterator as $item) {
            $items[] = $item;
        }
        return $items;
    }

    /**
     * Map over items in a synchronous iterator.
     *
     * Returns a new generator that yields transformed items.
     *
     * @template T
     * @template U
     * @param iterable<T> $iterator The iterator to map over
     * @param callable(T): U $callback Function to apply to each item
     * @return Generator<U> Generator of transformed items
     */
    public static function mapSyncIterator(iterable $iterator, callable $callback): Generator
    {
        foreach ($iterator as $item) {
            yield $callback($item);
        }
    }

    /**
     * Filter items in a synchronous iterator.
     *
     * Returns a new generator that yields only items matching the predicate.
     *
     * @template T
     * @param iterable<T> $iterator The iterator to filter
     * @param callable(T): bool $predicate Function to test each item
     * @return Generator<T> Generator of filtered items
     */
    public static function filterSyncIterator(iterable $iterator, callable $predicate): Generator
    {
        foreach ($iterator as $item) {
            if ($predicate($item)) {
                yield $item;
            }
        }
    }

    /**
     * Take n items from a synchronous iterator.
     *
     * Returns a new generator that yields at most n items.
     *
     * @template T
     * @param iterable<T> $iterator The iterator to take from
     * @param int $count Maximum number of items to yield
     * @return Generator<T> Generator of at most count items
     */
    public static function takeSyncIterator(iterable $iterator, int $count): Generator
    {
        $taken = 0;
        foreach ($iterator as $item) {
            if ($taken >= $count) {
                break;
            }
            yield $item;
            $taken++;
        }
    }

    /**
     * Skip n items from a synchronous iterator.
     *
     * Returns a new generator that skips the first n items.
     *
     * @template T
     * @param iterable<T> $iterator The iterator to skip from
     * @param int $count Number of items to skip
     * @return Generator<T> Generator of items after skipping
     */
    public static function skipSyncIterator(iterable $iterator, int $count): Generator
    {
        $skipped = 0;
        foreach ($iterator as $item) {
            if ($skipped < $count) {
                $skipped++;
                continue;
            }
            yield $item;
        }
    }

    /**
     * Consume a sequence of asynchronous values represented as Amp futures.
     *
     * @param iterable<mixed, Future<mixed>|callable():Future<mixed>|mixed> $iterator
     * @return Future<void>
     */
    public static function consumeAsyncIterator(iterable $iterator): Future
    {
        return async(static function () use ($iterator): void {
            foreach ($iterator as $item) {
                self::awaitMaybeFuture($item);
            }
        });
    }

    /**
     * Collect resolved values from an async iterator sequence.
     *
     * @template TKey of array-key
     * @template TValue
     * @param iterable<TKey, Future<TValue>|callable():Future<TValue>|TValue> $iterator
     * @return Future<array<TKey, TValue>>
     */
    public static function collectAsyncIterator(iterable $iterator): Future
    {
        return async(static function () use ($iterator): array {
            $results = [];
            foreach ($iterator as $key => $item) {
                $results[$key] = self::awaitMaybeFuture($item);
            }

            return $results;
        });
    }

    /**
     * Resolve a future or callable returning a future, returning its value.
     */
    private static function awaitMaybeFuture(mixed $value): mixed
    {
        if ($value instanceof Future) {
            return $value->await();
        }

        if (is_callable($value)) {
            $result = $value();
            if ($result instanceof Future) {
                return $result->await();
            }

            return $result;
        }

        return $value;
    }
}
