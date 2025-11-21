<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta error response
 */
class BetaErrorResponse
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $error,
    ) {
    }
}
