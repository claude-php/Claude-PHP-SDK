<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta billing error
 */
class BetaBillingError
{
    public function __construct(
        public readonly string $type,
        public readonly string $message,
    ) {
    }
}
