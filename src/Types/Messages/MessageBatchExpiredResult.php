<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

/**
 * Expired result from a message batch request
 *
 * @readonly
 */
class MessageBatchExpiredResult extends MessageBatchResult
{
    public function __construct()
    {
        parent::__construct('expired');
    }
}
