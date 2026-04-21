<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class CacheCreationUsage
{
    public function __construct(
        public readonly int $ephemeral_5m_input_tokens = 0,
        public readonly int $ephemeral_1h_input_tokens = 0,
    ) {
    }
}
