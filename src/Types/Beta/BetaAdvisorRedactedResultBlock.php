<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Redacted result block from an advisor tool invocation.
 */
class BetaAdvisorRedactedResultBlock
{
    public function __construct(
        public readonly string $type = 'advisor_redacted_result',
        public readonly ?string $id = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'advisor_redacted_result',
            id: $data['id'] ?? null,
        );
    }
}
