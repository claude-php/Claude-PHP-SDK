<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

/**
 * Parameters for creating a managed agent session.
 *
 * Mirrors Python `session_create_params.py`.
 */
class SessionCreateParams
{
    /**
     * @param string|array<string, mixed> $agent Agent ID string or agent object
     * @param string $environment_id Environment ID
     * @param array<string, string>|null $metadata Key-value metadata (max 16 pairs)
     * @param list<array<string, mixed>>|null $resources Resources to mount (file/github)
     * @param string|null $title Human-readable session title
     * @param list<string>|null $vault_ids Vault IDs for stored credentials
     */
    public function __construct(
        public readonly string|array $agent,
        public readonly string $environment_id,
        public readonly ?array $metadata = null,
        public readonly ?array $resources = null,
        public readonly ?string $title = null,
        public readonly ?array $vault_ids = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'agent' => $this->agent,
            'environment_id' => $this->environment_id,
            'metadata' => $this->metadata,
            'resources' => $this->resources,
            'title' => $this->title,
            'vault_ids' => $this->vault_ids,
        ], static fn ($v) => null !== $v);
    }
}
