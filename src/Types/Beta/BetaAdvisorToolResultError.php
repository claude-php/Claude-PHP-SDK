<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Error from an advisor tool invocation.
 */
class BetaAdvisorToolResultError
{
    public function __construct(
        public readonly string $type = 'advisor_error',
        public readonly ?string $error_code = null,
        public readonly ?string $message = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'advisor_error',
            error_code: $data['error_code'] ?? null,
            message: $data['message'] ?? null,
        );
    }
}
