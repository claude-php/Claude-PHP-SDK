<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta compaction block (server-side context compaction summary).
 *
 * Returned when autocompact is triggered. When `content` is null, the
 * compaction failed to produce a valid summary; clients may round-trip
 * compaction blocks with null content and the server treats them as no-ops.
 *
 * Mirrors Python `src/anthropic/types/beta/beta_compaction_block.py`.
 */
class BetaCompactionBlock
{
    /**
     * @param string $type Discriminator (always "compaction").
     * @param null|string $content Summary of compacted content, or null if compaction failed.
     * @param null|string $encrypted_content Opaque metadata from prior compaction, to be round-tripped verbatim.
     */
    public function __construct(
        public readonly string $type = 'compaction',
        public readonly ?string $content = null,
        public readonly ?string $encrypted_content = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'compaction',
            content: $data['content'] ?? null,
            encrypted_content: $data['encrypted_content'] ?? null,
        );
    }
}
