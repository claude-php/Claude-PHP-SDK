<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta web search tool result block
 */
class BetaWebSearchToolResultBlock
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $url = null,
        public readonly mixed $content = null,
    ) {}
}
