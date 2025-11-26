<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * MCP Tool Default Config param
 *
 * Default configuration applied to all tools from an MCP server.
 */
class MCPToolDefaultConfigParam
{
    /**
     * @param null|bool $defer_loading If true, tools will not be included in initial system prompt by default
     * @param null|bool $enabled Whether tools are enabled by default
     */
    public function __construct(
        public readonly ?bool $defer_loading = null,
        public readonly ?bool $enabled = null,
    ) {
    }
}
