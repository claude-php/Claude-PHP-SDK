<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

/**
 * Parameters for creating a vault.
 *
 * Mirrors Python `vault_create_params.py`.
 */
class VaultCreateParams
{
    /**
     * @param string $display_name Human-readable name (1-255 characters)
     * @param array<string, string>|null $metadata Key-value metadata (max 16 pairs)
     */
    public function __construct(
        public readonly string $display_name,
        public readonly ?array $metadata = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'display_name' => $this->display_name,
            'metadata' => $this->metadata,
        ], static fn ($v) => null !== $v);
    }
}
