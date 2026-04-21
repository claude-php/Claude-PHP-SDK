<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Session event (discriminated union by 'type' field).
 *
 * Possible types include: message, tool_use, tool_result, status_change,
 * error, thinking, text, and more. The full set depends on the API version.
 * Unknown types are preserved as-is via the $data property.
 */
class SessionEvent
{
    public function __construct(
        public readonly string $type = '',
        public readonly ?string $id = null,
        public readonly ?string $session_id = null,
        public readonly ?string $created_at = null,
        public readonly array $data = [],
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? '',
            id: $data['id'] ?? null,
            session_id: $data['session_id'] ?? null,
            created_at: $data['created_at'] ?? null,
            data: $data,
        );
    }
}
