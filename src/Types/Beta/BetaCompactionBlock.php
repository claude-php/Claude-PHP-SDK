<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta compaction block (server-side context compaction summary).
 */
class BetaCompactionBlock
{
    public function __construct(
        public readonly string $type = 'compaction',
        public readonly ?string $summary = null,
        public readonly ?int $compacted_tokens = null,
        public readonly ?int $remaining_tokens = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'compaction',
            summary: $data['summary'] ?? null,
            compacted_tokens: $data['compacted_tokens'] ?? null,
            remaining_tokens: $data['remaining_tokens'] ?? null,
        );
    }
}
