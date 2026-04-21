<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta user profile trust grant.
 */
class BetaUserProfileTrustGrant
{
    public function __construct(
        public readonly string $type = 'trust_grant',
        public readonly ?string $id = null,
        public readonly ?string $scope = null,
        public readonly ?string $granted_at = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'trust_grant',
            id: $data['id'] ?? null,
            scope: $data['scope'] ?? null,
            granted_at: $data['granted_at'] ?? null,
        );
    }
}
