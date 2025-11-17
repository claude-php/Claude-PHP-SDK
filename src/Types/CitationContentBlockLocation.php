<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Citation content block location
 */
class CitationContentBlockLocation
{
    public function __construct(
        public readonly string $type,
        public readonly string $cited_text,
        public readonly int $document_index,
        public readonly int $start_block_index,
        public readonly int $end_block_index,
        public readonly ?string $document_title = null,
        public readonly ?string $file_id = null,
    ) {}
}
