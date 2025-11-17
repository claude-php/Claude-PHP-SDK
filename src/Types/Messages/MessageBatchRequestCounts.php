<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

/**
 * Counts of requests by status in a message batch
 *
 * @readonly
 */
class MessageBatchRequestCounts
{
    /**
     * @param int $processing Number of requests currently being processed
     * @param int $succeeded Number of requests that completed successfully
     * @param int $errored Number of requests that encountered errors
     * @param int $canceled Number of requests that were canceled
     * @param int $expired Number of requests that expired
     */
    public function __construct(
        public readonly int $processing,
        public readonly int $succeeded,
        public readonly int $errored,
        public readonly int $canceled,
        public readonly int $expired,
    ) {}
}