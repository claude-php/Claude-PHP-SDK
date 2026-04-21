<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class McpServerUrlDefinition
{
    public function __construct(
        public readonly string $name = '',
        public readonly string $url = '',
        public readonly ?string $vault_credential_id = null,
    ) {
    }
}
