<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

/**
 * Individual response in a message batch
 *
 * @readonly
 */
class MessageBatchIndividualResponse
{
    /**
     * @param string $custom_id Custom ID provided in the request
     * @param MessageBatchResult $result The result of processing this individual request
     */
    public function __construct(
        public readonly string $custom_id,
        public readonly MessageBatchResult $result,
    ) {
    }
}
