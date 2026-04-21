<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaThinkingCapability
{
    public function __construct(
        public readonly string $support = BetaCapabilitySupport::UNSUPPORTED,
        public readonly ?array $allowed_values = null,
    ) {
    }
}
