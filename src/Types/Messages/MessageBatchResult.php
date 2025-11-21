<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

/**
 * Base class for message batch results
 *
 * @readonly
 */
abstract class MessageBatchResult
{
    /**
     * @param string $type The type of result
     */
    public function __construct(
        public readonly string $type,
    ) {
    }
}
