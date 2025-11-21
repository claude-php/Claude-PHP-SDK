<?php

declare(strict_types=1);

namespace ClaudePhp\Resources;

use Amp\Future;
use ClaudePhp\Utils\AsyncUtils;
use ClaudePhp\Utils\LazyProxy;

/**
 * Lazy proxy that exposes asynchronous wrappers for resource methods.
 *
 * Each proxied method call returns an Amp\Future that resolves with the same
 * value as the synchronous resource method.
 */
final class AsyncResourceProxy extends LazyProxy
{
    public function __construct(private Resource $resource)
    {
    }

    /**
     * Forward method calls to the proxied resource and wrap the invocation in an Amp Future.
     *
     * @return Future<mixed>
     */
    public function __call(string $name, array $arguments): Future
    {
        $resource = $this->getProxied();
        if (!method_exists($resource, $name)) {
            throw new \BadMethodCallException(sprintf(
                'Method %s::%s does not exist on resource of type %s',
                $resource::class,
                $name,
                $resource::class,
            ));
        }

        $wrapped = AsyncUtils::asyncify([$resource, $name]);

        // @var Future<mixed>
        return $wrapped(...$arguments);
    }

    protected function load(): object
    {
        return $this->resource;
    }
}
