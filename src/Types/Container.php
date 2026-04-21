<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Container metadata returned with messages.
 */
class Container
{
    public function __construct(
        public readonly string $id = '',
        public readonly ?string $expires_at = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            expires_at: $data['expires_at'] ?? null,
        );
    }
}
