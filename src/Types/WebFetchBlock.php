<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * A web fetch result block returned by the web_fetch tool.
 *
 * Contains the fetched document content and metadata.
 */
class WebFetchBlock
{
    /**
     * @param mixed       $content      The fetched document content (DocumentBlock)
     * @param string      $url          The URL that was fetched
     * @param string|null $retrieved_at ISO 8601 timestamp when the content was retrieved
     * @param string      $type         Always "web_fetch_result"
     */
    public function __construct(
        public readonly mixed $content,
        public readonly string $url,
        public readonly ?string $retrieved_at = null,
        public readonly string $type = 'web_fetch_result',
    ) {
    }
}
