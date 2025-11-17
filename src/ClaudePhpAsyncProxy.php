<?php

declare(strict_types=1);

namespace ClaudePhp;

use ClaudePhp\Resources\AsyncResourceProxy;
use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\LazyProxy;

/**
 * Client-level async proxy that lazily wraps resource accessors.
 *
 * Calling resource accessors on this proxy returns the corresponding AsyncResourceProxy,
 * enabling `$client->async()->messages()->create(...)` style usage.
 */
final class ClaudePhpAsyncProxy extends LazyProxy
{
    public function __construct(private ClaudePhp $client)
    {
    }

    protected function load(): object
    {
        return $this->client;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $client = $this->getProxied();
        if (!method_exists($client, $name)) {
            throw new \BadMethodCallException(sprintf('Method %s::%s does not exist', $client::class, $name));
        }

        $result = $client->$name(...$arguments);

        if ($result instanceof Resource) {
            return $result->async();
        }

        return $result;
    }
}
