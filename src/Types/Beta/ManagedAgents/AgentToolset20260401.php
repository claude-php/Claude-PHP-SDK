<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Anthropic-provided agent toolset (e.g. computer use, code execution).
 */
class AgentToolset20260401
{
    public function __construct(
        public readonly string $type = 'anthropic_toolset_20260401',
        public readonly string $name = '',
        public readonly ?array $tool_configs = null,
        public readonly ?array $default_config = null,
    ) {
    }
}
