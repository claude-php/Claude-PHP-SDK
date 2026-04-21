<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Compaction content block delta (streamed compaction summary text).
 */
class BetaCompactionContentBlockDelta
{
    public function __construct(
        public readonly string $type = 'compaction_delta',
        public readonly string $partial_summary = '',
    ) {
    }
}
