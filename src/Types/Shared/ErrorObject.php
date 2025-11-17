<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Shared;

/**
 * Standard error object returned by the API
 */
class ErrorObject
{
    public function __construct(
        public readonly string $type,
        public readonly string $message,
    ) {}
}
