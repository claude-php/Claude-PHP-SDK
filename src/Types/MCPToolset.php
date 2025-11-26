<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * MCP Toolset
 *
 * Configuration for MCP (Model Context Protocol) server tools.
 */
class MCPToolset
{
    /**
     * @param string $type The toolset type (mcp_toolset)
     * @param string $mcp_server_name Name of the MCP server to configure tools for
     * @param null|CacheControlEphemeral $cache_control Cache control configuration
     * @param null|array<string, MCPToolConfig> $configs Configuration overrides for specific tools, keyed by tool name
     * @param null|MCPToolDefaultConfig $default_config Default configuration applied to all tools from this server
     */
    public function __construct(
        public readonly string $type,
        public readonly string $mcp_server_name,
        public readonly ?CacheControlEphemeral $cache_control = null,
        public readonly ?array $configs = null,
        public readonly ?MCPToolDefaultConfig $default_config = null,
    ) {
    }
}
