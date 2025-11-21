<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta API error
 */
class BetaAPIError
{
    public function __construct(
        public readonly string $type,
        public readonly string $message,
    ) {
    }
}
