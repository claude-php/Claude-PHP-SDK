<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Unrestricted network configuration.
 */
class BetaUnrestrictedNetwork
{
    public function __construct(
        public readonly string $type = 'unrestricted',
    ) {
    }
}
