<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for ToolTextEditor20250429
 *
 * @readonly
 */
class ToolTextEditor20250429Param
{
    /**
     * @param string $type The type identifier ("text_editor_20250429")
     * @param array<string, mixed> $input_schema JSON schema for tool inputs
     * @param string|null $cache_control Cache control settings
     */
    public function __construct(
        public readonly string $type,
        public readonly array $input_schema,
        public readonly ?array $cache_control = null,
    ) {}
}
