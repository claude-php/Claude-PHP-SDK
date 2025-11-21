<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Web search tool result error
 */
class WebSearchToolResultError
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $error_code = null,
        public readonly ?string $error_message = null,
    ) {
    }
}
