<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Event fired when a content block delta is received during streaming
 *
 * @readonly
 */
class ContentBlockDeltaEvent
{
    /**
     * @param string $type Event type ("content_block_delta")
     * @param int $index Index of the content block being updated
     * @param array<string, mixed> $delta The delta update for the content block
     */
    public function __construct(
        public readonly string $type,
        public readonly int $index,
        public readonly array $delta,
    ) {}
}