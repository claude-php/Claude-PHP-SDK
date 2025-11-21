<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Citation page location
 */
class CitationPageLocation
{
    public function __construct(
        public readonly string $type,
        public readonly string $cited_text,
        public readonly int $document_index,
        public readonly int $start_page_number,
        public readonly int $end_page_number,
        public readonly ?string $document_title = null,
        public readonly ?string $file_id = null,
    ) {
    }
}
