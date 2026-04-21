<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Result block from an advisor tool invocation.
 */
class BetaAdvisorResultBlock
{
    public function __construct(
        public readonly string $type = 'advisor_result',
        public readonly ?string $id = null,
        public readonly ?array $content = null,
        public readonly ?array $usage = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'advisor_result',
            id: $data['id'] ?? null,
            content: $data['content'] ?? null,
            usage: $data['usage'] ?? null,
        );
    }
}
