<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Web search result block param for request
 */
class WebSearchResultBlockParam
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $url = null,
        public readonly ?string $title = null,
        public readonly ?string $snippet = null,
    ) {
    }
}
