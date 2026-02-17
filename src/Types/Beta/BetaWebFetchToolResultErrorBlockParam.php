<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta web fetch tool result error block parameter.
 */
class BetaWebFetchToolResultErrorBlockParam
{
    /**
     * @param string $error_code Error code
     * @param string $type       Always "web_fetch_tool_result_error"
     */
    public function __construct(
        public readonly string $error_code,
        public readonly string $type = 'web_fetch_tool_result_error',
    ) {
    }
}
