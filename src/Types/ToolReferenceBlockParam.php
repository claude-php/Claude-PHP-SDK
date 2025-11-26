<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool reference block param
 *
 * A reference to a tool that can be used in tool search results.
 */
class ToolReferenceBlockParam
{
    /**
     * @param string $type The block type (tool_reference)
     * @param string $tool_name The name of the referenced tool
     * @param null|CacheControlEphemeralParam $cache_control Cache control configuration
     */
    public function __construct(
        public readonly string $type,
        public readonly string $tool_name,
        public readonly ?CacheControlEphemeralParam $cache_control = null,
    ) {
    }
}
