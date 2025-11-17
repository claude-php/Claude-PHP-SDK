<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Event fired when a content block starts during streaming
 *
 * @readonly
 */
class ContentBlockStartEvent
{
    /**
     * @param string $type Event type ("content_block_start")
     * @param int $index Index of the content block being started
     * @param array<string, mixed> $content_block The content block that is starting
     */
    public function __construct(
        public readonly string $type,
        public readonly int $index,
        public readonly array $content_block,
    ) {}
}