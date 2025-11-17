<?php

declare(strict_types=1);

namespace ClaudePhp\Responses;

/**
 * Represents a complete message response from the API
 */
class Message
{
    /**
     * @param string $id The unique identifier for this message
     * @param string $type The type of object (always 'message')
     * @param string $role The role of the message creator (always 'assistant')
     * @param array<int, TextContent|ToolUseContent|ToolResultContent> $content The content blocks in this message
     * @param string $model The model used to generate this message
     * @param string|null $stop_reason The reason the model stopped generating
     * @param string|null $stop_sequence The stop sequence that was matched (if applicable)
     * @param Usage|null $usage Token usage information
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $role,
        public readonly array $content,
        public readonly string $model,
        public readonly ?string $stop_reason,
        public readonly ?string $stop_sequence = null,
        public readonly ?Usage $usage = null
    ) {
    }
}
