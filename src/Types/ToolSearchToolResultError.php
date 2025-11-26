<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool search tool result error
 *
 * Contains error information from a tool search.
 */
class ToolSearchToolResultError
{
    /**
     * @param string $type The block type (tool_search_tool_result_error)
     * @param string $error_code Error code (invalid_tool_input, unavailable, too_many_requests, execution_time_exceeded)
     * @param null|string $error_message Optional error message
     */
    public function __construct(
        public readonly string $type,
        public readonly string $error_code,
        public readonly ?string $error_message = null,
    ) {
    }
}
