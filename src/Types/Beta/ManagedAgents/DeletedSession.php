<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Response for a deleted session.
 */
class DeletedSession
{
    public function __construct(
        public readonly string $id = '',
        public readonly string $type = 'session_deleted',
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            type: $data['type'] ?? 'session_deleted',
        );
    }
}
