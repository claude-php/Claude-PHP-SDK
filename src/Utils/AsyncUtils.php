<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use function Amp\async;

use Amp\Future;

/**
 * Async helper utilities backed by AMPHP.
 *
 * Provides parity with the Python SDK's async helpers by exposing the
 * current async runtime and wrapping synchronous callables in Futures.
 */
final class AsyncUtils
{
    /**
     * Detect the currently running async library.
     *
     * Returns "amphp" when the Revolt event loop driver is running inside
     * an AMPHP context, otherwise returns the string "false" to match
     * the Python SDK's behavior.
     */
    public static function getAsyncLibrary(): string
    {
        if (class_exists(\Fiber::class) && null !== \Fiber::getCurrent()) {
            return 'amphp';
        }

        return 'false';
    }

    /**
     * Wrap a callable in an async-ready closure that returns an Amp\Future.
     *
     * @template TReturn
     *
     * @param callable(...$args):TReturn $func
     *
     * @return callable(...$args):Future<TReturn>
     */
    public static function asyncify(callable $func): callable
    {
        return static function (mixed ...$args) use ($func): Future {
            return async(static fn () => $func(...$args));
        };
    }

    /**
     * Await a set of futures sequentially and return their resolved values.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey, Future<TValue>> $futures
     *
     * @return array<TKey, TValue>
     */
    public static function awaitAll(iterable $futures): array
    {
        $results = [];
        foreach ($futures as $key => $future) {
            if (!$future instanceof Future) {
                throw new \InvalidArgumentException('All items passed to awaitAll must be instances of Amp\Future.');
            }
            $results[$key] = $future->await();
        }

        return $results;
    }
}
