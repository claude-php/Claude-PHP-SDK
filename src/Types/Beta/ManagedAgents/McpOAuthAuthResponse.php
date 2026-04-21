<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class McpOAuthAuthResponse
{
    public function __construct(
        public readonly string $type = 'mcp_oauth',
        public readonly ?string $authorization_url = null,
        public readonly ?string $client_id = null,
    ) {
    }
}
