<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta user profile enrollment URL response.
 */
class BetaUserProfileEnrollmentUrl
{
    public function __construct(
        public readonly string $url = '',
        public readonly ?string $expires_at = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'] ?? '',
            expires_at: $data['expires_at'] ?? null,
        );
    }
}
