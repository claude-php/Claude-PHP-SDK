<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * A tool result block containing the output of a web_fetch tool call.
 *
 * Returned in the API response when the model invokes a web_fetch tool.
 */
class WebFetchToolResultBlock
{
    /**
     * @param mixed       $content      The web fetch result or error (WebFetchBlock or WebFetchToolResultErrorBlock)
     * @param string      $tool_use_id  The ID of the tool_use block this result corresponds to
     * @param mixed|null  $caller       Information about what called this tool
     * @param string      $type         Always "web_fetch_tool_result"
     */
    public function __construct(
        public readonly mixed $content,
        public readonly string $tool_use_id,
        public readonly mixed $caller = null,
        public readonly string $type = 'web_fetch_tool_result',
    ) {
    }
}
