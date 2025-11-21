<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Cache control ephemeral
 */
class CacheControlEphemeral
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
