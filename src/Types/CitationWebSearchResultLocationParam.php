<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Citation web search result location param
 */
class CitationWebSearchResultLocationParam
{
    public function __construct(
        public readonly string $type,
        public readonly string $cited_text,
        public readonly int $document_index,
        public readonly ?string $document_title = null,
    ) {
    }
}
