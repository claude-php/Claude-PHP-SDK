<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

use ClaudePhp\Types\Shared\ErrorResponse;

/**
 * Error result from a message batch request
 *
 * @readonly
 */
class MessageBatchErroredResult extends MessageBatchResult
{
    /**
     * @param ErrorResponse $error The error that occurred during processing
     */
    public function __construct(
        public readonly ErrorResponse $error,
    ) {
        parent::__construct('errored');
    }
}
