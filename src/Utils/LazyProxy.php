<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

/**
 * Base class for implementing lazy proxies that defer instantiation of the underlying object.
 *
 * This mirrors the Python SDK's LazyProxy helper, forwarding property and method access to
 * the proxied object while only loading it when first needed.
 */
abstract class LazyProxy
{
    private ?object $proxied = null;

    /**
     * Load the proxied object.
     *
     * @return object
     */
    abstract protected function load(): object;

    /**
     * Get the cached proxied object, loading it if necessary.
     */
    final protected function getProxied(): object
    {
        if (!$this->proxied) {
            $proxied = $this->load();
            if (!is_object($proxied)) {
                throw new \RuntimeException('LazyProxy::load() must return an object.');
            }
            $this->proxied = $proxied;
        }

        return $this->proxied;
    }

    /**
     * Forward dynamic method calls to the proxied object.
     */
    public function __call(string $name, array $arguments): mixed
    {
        $proxied = $this->getProxied();
        if (!method_exists($proxied, $name)) {
            throw new \BadMethodCallException(sprintf(
                'Method %s::%s does not exist on proxied object of type %s',
                static::class,
                $name,
                $proxied::class
            ));
        }

        return $proxied->$name(...$arguments);
    }

    /**
     * Forward property access to the proxied object.
     */
    public function __get(string $name): mixed
    {
        return $this->getProxied()->$name;
    }

    /**
     * Forward property writes to the proxied object.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->getProxied()->$name = $value;
    }

    /**
     * Forward isset() calls to the proxied object.
     */
    public function __isset(string $name): bool
    {
        return isset($this->getProxied()->$name);
    }

    /**
     * Forward unset() calls to the proxied object when the property exists.
     */
    public function __unset(string $name): void
    {
        $proxied = $this->getProxied();
        if (property_exists($proxied, $name)) {
            unset($proxied->$name);
        }
    }

    public function __toString(): string
    {
        return (string) $this->getProxied();
    }
}
