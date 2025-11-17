<?php

declare(strict_types=1);

namespace ClaudePhp\Responses;

/**
 * Represents tool use result in a message
 */
class ToolResultContent
{
    /**
     * @param string $tool_use_id The ID of the tool use this result is for
     * @param string|null $content The content of the tool result
     * @param bool $is_error Whether this result represents an error
     */
    public function __construct(
        public readonly string $tool_use_id,
        public readonly ?string $content = null,
        public readonly bool $is_error = false
    ) {
    }
}
