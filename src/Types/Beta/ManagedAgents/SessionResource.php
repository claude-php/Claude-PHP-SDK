<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class SessionResource
{
    public function __construct(
        public readonly string $id = '',
        public readonly string $type = '',
        public readonly ?string $created_at = null,
        public readonly array $data = [],
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            type: $data['type'] ?? '',
            created_at: $data['created_at'] ?? null,
            data: $data,
        );
    }
}
