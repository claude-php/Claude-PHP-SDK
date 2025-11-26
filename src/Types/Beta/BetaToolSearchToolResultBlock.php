<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta Tool Search Tool Result Block
 *
 * Contains the result of a tool search operation.
 */
class BetaToolSearchToolResultBlock
{
    /**
     * @param string $type The block type (tool_search_tool_result)
     * @param string $tool_use_id The tool use ID
     * @param BetaToolSearchToolResultError|BetaToolSearchToolSearchResultBlock $content The result content
     */
    public function __construct(
        public readonly string $type,
        public readonly string $tool_use_id,
        public readonly BetaToolSearchToolResultError|BetaToolSearchToolSearchResultBlock $content,
    ) {
    }
}
