<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Citation location types
 */
class CitationCharLocation
{
    public function __construct(
        public readonly string $type,
        public readonly string $cited_text,
        public readonly int $document_index,
        public readonly int $start_char_index,
        public readonly int $end_char_index,
        public readonly ?string $document_title = null,
        public readonly ?string $file_id = null,
    ) {
    }
}
