<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Messages;

/**
 * Parameters for listing message batches
 *
 * @readonly
 */
class BatchListParams
{
    /**
     * @param string|null $before_id ID to list batches before
     * @param string|null $after_id ID to list batches after  
     * @param int|null $limit Maximum number of batches to return
     */
    public function __construct(
        public readonly ?string $before_id = null,
        public readonly ?string $after_id = null,
        public readonly ?int $limit = null,
    ) {}
}