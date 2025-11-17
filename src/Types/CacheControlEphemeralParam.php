<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Cache control ephemeral param
 */
class CacheControlEphemeralParam
{
    public function __construct(
        public readonly string $type,
    ) {}
}
