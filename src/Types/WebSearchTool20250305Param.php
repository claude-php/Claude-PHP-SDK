<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for WebSearchTool20250305
 *
 * @readonly
 */
class WebSearchTool20250305Param
{
    /**
     * @param string $type The type identifier ("web_search_20250305")
     * @param array<string, mixed> $input_schema JSON schema for tool inputs
     * @param string|null $cache_control Cache control settings
     */
    public function __construct(
        public readonly string $type,
        public readonly array $input_schema,
        public readonly ?array $cache_control = null,
    ) {}
}
