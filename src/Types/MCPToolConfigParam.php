<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * MCP Tool Config param
 *
 * Configuration for a specific MCP tool.
 */
class MCPToolConfigParam
{
    /**
     * @param null|bool $defer_loading If true, tool will not be included in initial system prompt
     * @param null|bool $enabled Whether the tool is enabled
     */
    public function __construct(
        public readonly ?bool $defer_loading = null,
        public readonly ?bool $enabled = null,
    ) {
    }
}
