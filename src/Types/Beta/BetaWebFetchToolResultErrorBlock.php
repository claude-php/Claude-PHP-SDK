<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta web fetch tool result error block.
 */
class BetaWebFetchToolResultErrorBlock
{
    /**
     * @param string $error_code Error code (invalid_tool_input, url_too_long, url_not_allowed, etc.)
     * @param string $type       Always "web_fetch_tool_result_error"
     */
    public function __construct(
        public readonly string $error_code,
        public readonly string $type = 'web_fetch_tool_result_error',
    ) {
    }
}
