<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Shared;

class AuthenticationError
{
    public function __construct(
        public readonly string $type,
        public readonly string $message,
    ) {
    }
}
