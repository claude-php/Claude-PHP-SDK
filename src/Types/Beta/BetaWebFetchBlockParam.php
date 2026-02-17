<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta web fetch block parameter.
 */
class BetaWebFetchBlockParam
{
    /**
     * @param mixed       $content      The fetched document content
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
