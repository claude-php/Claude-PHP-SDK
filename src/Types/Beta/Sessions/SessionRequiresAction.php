<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Sessions;

class SessionRequiresAction
{
    public function __construct(
        public readonly string $type = 'session_requires_action',
        public readonly ?string $action = null,
        public readonly ?array $context = null,
    ) {
    }
}
