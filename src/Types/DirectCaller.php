<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Direct Caller
 *
 * Represents a direct tool caller.
 */
class DirectCaller
{
    /**
     * @param string $type The caller type (direct)
     */
    public function __construct(
        public readonly string $type,
    ) {
    }
}
