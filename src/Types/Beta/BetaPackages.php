<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta packages configuration for managed agents.
 */
class BetaPackages
{
    public function __construct(
        public readonly ?array $python = null,
        public readonly ?array $node = null,
    ) {
    }
}
