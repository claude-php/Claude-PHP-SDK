<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Direct Caller param
 *
 * Represents a direct tool caller.
 */
class DirectCallerParam
{
    /**
     * @param string $type The caller type (direct)
     */
    public function __construct(
        public readonly string $type,
    ) {
    }
}
