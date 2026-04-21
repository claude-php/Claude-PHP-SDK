<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Sessions;

class SessionErrorEvent
{
    public function __construct(
        public readonly string $type = 'session_error',
        public readonly ?string $error = null,
        public readonly ?array $details = null,
    ) {
    }
}
