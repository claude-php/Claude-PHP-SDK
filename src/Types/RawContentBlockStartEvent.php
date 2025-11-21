<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Raw content block start event
 */
class RawContentBlockStartEvent
{
    public function __construct(
        public readonly string $type,
        public readonly int $index,
        public readonly mixed $content_block,
    ) {
    }
}
