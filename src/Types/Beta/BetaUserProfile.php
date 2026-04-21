<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta user profile response object.
 */
class BetaUserProfile
{
    public function __construct(
        public readonly string $id = '',
        public readonly string $type = 'user_profile',
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $external_id = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
        public readonly ?array $metadata = null,
        public readonly ?array $trust_grants = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            type: $data['type'] ?? 'user_profile',
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            external_id: $data['external_id'] ?? null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
            metadata: $data['metadata'] ?? null,
            trust_grants: $data['trust_grants'] ?? null,
        );
    }
}
