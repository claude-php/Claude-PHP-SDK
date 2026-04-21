<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Vaults;

class McpOAuthCreateParams
{
    public function __construct(
        public readonly string $type = 'mcp_oauth',
        public readonly string $authorization_url = '',
        public readonly string $client_id = '',
        public readonly ?string $client_secret = null,
        public readonly ?string $token_url = null,
        public readonly ?string $display_name = null,
        public readonly ?array $scopes = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'authorization_url' => $this->authorization_url,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'token_url' => $this->token_url,
            'display_name' => $this->display_name,
            'scopes' => $this->scopes,
        ], static fn ($v) => null !== $v);
    }
}
