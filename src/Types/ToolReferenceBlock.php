<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool reference block
 *
 * A reference to a tool that can be used in tool search results.
 */
class ToolReferenceBlock
{
    /**
     * @param string $type The block type (tool_reference)
     * @param string $tool_name The name of the referenced tool
     */
    public function __construct(
        public readonly string $type,
        public readonly string $tool_name,
    ) {
    }
}
