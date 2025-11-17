<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Event fired when a content block stops during streaming
 *
 * @readonly
 */
class ContentBlockStopEvent
{
    /**
     * @param string $type Event type ("content_block_stop")
     * @param int $index Index of the content block that stopped
     */
    public function __construct(
        public readonly string $type,
        public readonly int $index,
    ) {}
}