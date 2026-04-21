<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

class ThinkingCapability
{
    public function __construct(
        public readonly string $support = CapabilitySupport::UNSUPPORTED,
        public readonly ?array $allowed_values = null,
    ) {
    }
}
