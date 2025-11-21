<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

/**
 * Parameters for creating a message batch
 *
 * @readonly
 */
class BatchCreateParams
{
    /**
     * @param array<array<string, mixed>> $requests Array of individual message requests
     */
    public function __construct(
        public readonly array $requests,
    ) {
    }
}
