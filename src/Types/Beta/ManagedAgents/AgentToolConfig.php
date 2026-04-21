<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Per-tool config override within an agent toolset.
 */
class AgentToolConfig
{
    public function __construct(
        public readonly string $name = '',
        public readonly ?array $confirmation_policy = null,
        public readonly ?bool $enabled = null,
    ) {
    }
}
