<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

/**
 * Canceled result from a message batch request
 *
 * @readonly
 */
class MessageBatchCanceledResult extends MessageBatchResult
{
    public function __construct()
    {
        parent::__construct('canceled');
    }
}
