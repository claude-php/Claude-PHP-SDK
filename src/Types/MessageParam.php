<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Message parameter for API requests
 *
 * Represents a single message in the conversation history
 *
 * @readonly
 */
class MessageParam
{
    /**
     * @param string $role The role of the message sender ("user" or "assistant")
     * @param array<array<string, mixed>|string> $content The message content (array of content blocks or plain string)
     */
    public function __construct(
        public readonly string $role,
        public readonly array|string $content,
    ) {
    }
}
