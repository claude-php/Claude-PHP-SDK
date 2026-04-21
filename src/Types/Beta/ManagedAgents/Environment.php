<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Managed agents environment response object.
 */
class Environment
{
    public function __construct(
        public readonly string $id = '',
        public readonly string $type = 'environment',
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $status = null,
        public readonly ?array $variables = null,
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
            'id', 'type', 'name', 'description', 'status', 'variables',
            'created_at', 'updated_at', 'archived_at', 'metadata',
        ])));
    }
}
