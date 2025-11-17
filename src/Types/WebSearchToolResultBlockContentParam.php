<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for WebSearchToolResultBlockContent
 *
 * @readonly
 */
class WebSearchToolResultBlockContentParam
{
    /**
     * @param string $type The type identifier ("tool_result")
     * @param string $tool_use_id The ID of the tool use this result is for
     * @param array<array<string, mixed>>|string|null $content The result content
     * @param bool|null $is_error Whether the result represents an error
     */
    public function __construct(
        public readonly string $type,
        public readonly string $tool_use_id,
        public readonly array|string|null $content = null,
        public readonly ?bool $is_error = null,
    ) {}
}
