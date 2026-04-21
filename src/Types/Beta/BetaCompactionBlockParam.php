<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta compaction block param.
 *
 * Round-trip these blocks from prior responses to maintain context across
 * compaction boundaries. When `content` is null, the block represents a
 * failed compaction; the server treats these as no-ops. Empty string
 * content is not allowed.
 *
 * Mirrors Python `src/anthropic/types/beta/beta_compaction_block_param.py`.
 */
class BetaCompactionBlockParam
{
    /**
     * @param string $type Discriminator (always "compaction").
     * @param null|string $content Summary of previously compacted content, or null if compaction failed.
     * @param null|array<string, mixed> $cache_control Cache control breakpoint (BetaCacheControlEphemeralParam shape).
     * @param null|string $encrypted_content Opaque metadata from prior compaction, to be round-tripped verbatim.
     */
    public function __construct(
        public readonly string $type = 'compaction',
        public readonly ?string $content = null,
        public readonly ?array $cache_control = null,
        public readonly ?string $encrypted_content = null,
    ) {
    }
}
