<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class StaticBearerAuthResponse
{
    public function __construct(
        public readonly string $type = 'static_bearer',
        public readonly ?string $token_preview = null,
    ) {
    }
}
