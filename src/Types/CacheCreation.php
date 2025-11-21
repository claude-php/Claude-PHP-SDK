<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Cache creation info
 */
class CacheCreation
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
