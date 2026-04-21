<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Limited network configuration (allow-list specific domains).
 */
class BetaLimitedNetwork
{
    public function __construct(
        public readonly string $type = 'limited',
        public readonly ?array $allowed_domains = null,
    ) {
    }
}
