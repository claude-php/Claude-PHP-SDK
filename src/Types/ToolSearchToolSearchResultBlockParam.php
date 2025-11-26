<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool search tool search result block param
 *
 * Contains the search results from a tool search.
 */
class ToolSearchToolSearchResultBlockParam
{
    /**
     * @param string $type The block type (tool_search_tool_search_result)
     * @param array<ToolReferenceBlockParam> $tool_references List of tool references found
     */
    public function __construct(
        public readonly string $type,
        public readonly array $tool_references,
    ) {
    }
}
