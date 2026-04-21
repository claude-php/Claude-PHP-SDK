<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Vault credential response object.
 */
class Credential
{
    public function __construct(
        public readonly string $id = '',
        public readonly string $type = 'credential',
        public readonly ?string $vault_id = null,
        public readonly ?string $name = null,
        public readonly ?string $credential_type = null,
        public readonly ?string $status = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
        public readonly ?string $archived_at = null,
        public readonly ?array $metadata = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(...array_intersect_key($data, array_flip([
            'id', 'type', 'vault_id', 'name', 'credential_type',
            'status', 'created_at', 'updated_at', 'archived_at', 'metadata',
        ])));
    }
}
