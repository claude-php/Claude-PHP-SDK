<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta Tool Search Tool Search Result Block
 *
 * Contains the search results from a tool search.
 */
class BetaToolSearchToolSearchResultBlock
{
    /**
     * @param string $type The block type (tool_search_tool_search_result)
     * @param array<BetaToolReferenceBlock> $tool_references List of tool references found
     */
    public function __construct(
        public readonly string $type,
        public readonly array $tool_references,
    ) {
    }
}
