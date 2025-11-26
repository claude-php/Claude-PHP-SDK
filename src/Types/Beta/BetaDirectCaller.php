<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta Direct Caller
 *
 * Represents a direct tool caller.
 */
class BetaDirectCaller
{
    /**
     * @param string $type The caller type (direct)
     */
    public function __construct(
        public readonly string $type,
    ) {
    }
}
