<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Base class for message stream events
 *
 * @readonly
 */
abstract class MessageStreamEvent
{
    /**
     * @param string $type The type of stream event
     */
    public function __construct(
        public readonly string $type,
    ) {}
}