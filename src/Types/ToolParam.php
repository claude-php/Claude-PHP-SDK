<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool parameter for API requests
 *
 * @readonly
 */
class ToolParam
{
    /**
     * @param string $type The tool type
     * @param string $name The tool name
     * @param null|string $description The tool description
     * @param null|array<string, mixed> $input_schema The input schema
     * @param null|array<string, mixed> $cache_control Cache control configuration
     */
    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?array $input_schema = null,
        public readonly ?array $cache_control = null,
    ) {
    }
}
