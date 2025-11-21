<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

use ClaudePhp\Types\Message;

/**
 * Successful result from a message batch request
 *
 * @readonly
 */
class MessageBatchSucceededResult extends MessageBatchResult
{
    /**
     * @param Message $message The successfully processed message
     */
    public function __construct(
        public readonly Message $message,
    ) {
        parent::__construct('succeeded');
    }
}
