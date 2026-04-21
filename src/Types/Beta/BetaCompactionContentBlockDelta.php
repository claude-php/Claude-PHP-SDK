<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Compaction content block delta (streamed compaction summary text).
 *
 * Mirrors Python `src/anthropic/types/beta/beta_compaction_content_block_delta.py`.
 */
class BetaCompactionContentBlockDelta
{
    /**
     * @param string $type Discriminator (always "compaction_delta").
     * @param null|string $content Partial compaction summary text.
     * @param null|string $encrypted_content Opaque metadata from prior compaction, to be round-tripped verbatim.
     */
    public function __construct(
        public readonly string $type = 'compaction_delta',
        public readonly ?string $content = null,
        public readonly ?string $encrypted_content = null,
    ) {
    }
}
