<?php

declare(strict_types=1);

namespace ClaudePhp\Responses;

/**
 * Represents tool use content block in a message
 */
class ToolUseContent
{
    /**
     * @param string $id The unique identifier for this tool use
     * @param string $name The name of the tool being used
     * @param array<string, mixed> $input The input provided to the tool
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $input,
    ) {
    }
}
