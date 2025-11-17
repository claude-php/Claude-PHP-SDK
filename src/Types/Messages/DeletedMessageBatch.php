<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

/**
 * Response when a message batch has been deleted
 *
 * @readonly
 */
class DeletedMessageBatch
{
    /**
     * @param string $id The ID of the deleted message batch
     * @param string $type Object type ("message_batch_deleted")
     * @param bool $deleted Whether the deletion was successful
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly bool $deleted,
    ) {}
}