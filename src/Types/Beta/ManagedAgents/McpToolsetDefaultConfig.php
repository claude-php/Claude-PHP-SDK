<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class McpToolsetDefaultConfig
{
    public function __construct(
        public readonly ?array $confirmation_policy = null,
        public readonly ?bool $enabled = null,
    ) {
    }
}
