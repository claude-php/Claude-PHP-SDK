<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

use ClaudePhp\Types\Messages\MessageBatchRequestCounts;

/**
 * Message batch object for processing multiple messages in a single request
 *
 * @readonly
 */
class MessageBatch
{
    /**
     * @param string $id Unique identifier for the message batch
     * @param string $type Object type ("message_batch")
     * @param string $processing_status Current processing status
     * @param MessageBatchRequestCounts $request_counts Statistics about the requests in the batch
     * @param string $created_at ISO 8601 timestamp of when the batch was created
     * @param string|null $expires_at ISO 8601 timestamp of when the batch expires
     * @param string|null $archived_at ISO 8601 timestamp of when the batch was archived
     * @param string|null $cancel_initiated_at ISO 8601 timestamp of when cancellation was initiated
     * @param string|null $ended_at ISO 8601 timestamp of when the batch completed processing
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $processing_status,
        public readonly MessageBatchRequestCounts $request_counts,
        public readonly string $created_at,
        public readonly ?string $expires_at = null,
        public readonly ?string $archived_at = null,
        public readonly ?string $cancel_initiated_at = null,
        public readonly ?string $ended_at = null,
    ) {}
}