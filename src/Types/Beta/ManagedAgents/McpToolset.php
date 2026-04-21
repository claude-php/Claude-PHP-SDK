<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * MCP toolset definition for managed agents.
 */
class McpToolset
{
    public function __construct(
        public readonly ?string $server_url = null,
        public readonly ?array $tool_config = null,
        public readonly ?array $default_config = null,
    ) {
    }
}
