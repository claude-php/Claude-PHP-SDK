<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Shared;

/**
 * Error response from the API
 */
class ErrorResponse
{
    public function __construct(
        public readonly string $type,
        public readonly ErrorObject $error,
    ) {
    }
}
