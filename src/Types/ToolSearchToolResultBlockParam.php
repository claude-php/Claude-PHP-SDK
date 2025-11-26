<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool search tool result block param
 *
 * Contains the result of a tool search operation.
 */
class ToolSearchToolResultBlockParam
{
    /**
     * @param string $type The block type (tool_search_tool_result)
     * @param string $tool_use_id The tool use ID
     * @param ToolSearchToolResultErrorParam|ToolSearchToolSearchResultBlockParam $content The result content
     */
    public function __construct(
        public readonly string $type,
        public readonly string $tool_use_id,
        public readonly ToolSearchToolResultErrorParam|ToolSearchToolSearchResultBlockParam $content,
    ) {
    }
}
