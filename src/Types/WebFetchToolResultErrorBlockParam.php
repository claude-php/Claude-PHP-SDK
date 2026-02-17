<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Web fetch tool result error block parameter (for constructing error results).
 */
class WebFetchToolResultErrorBlockParam
{
    /**
     * @param string $error_code Error code (see WebFetchToolResultErrorCode constants)
     * @param string $type       Always "web_fetch_tool_result_error"
     */
    public function __construct(
        public readonly string $error_code,
        public readonly string $type = 'web_fetch_tool_result_error',
    ) {
    }
}
