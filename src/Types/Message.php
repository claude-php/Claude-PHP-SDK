<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Message response from the Anthropic API
 *
 * @readonly
 */
class Message
{
    /**
     * @param string $id The unique identifier for the message
     * @param string $type The type of object ("message")
     * @param string $role The role of the message sender ("assistant" or "user")
     * @param array<array<string, mixed>> $content The message content blocks
     * @param string $model The model that generated the response
     * @param string $stop_reason Why the model stopped generating text
     * @param null|string $stop_sequence The stop sequence that triggered the halt (if any)
     * @param Usage $usage Token usage information
     * @param null|array<string, mixed> $stop_details Structured stop details (e.g. refusal)
     * @param null|array<string, mixed> $container Container metadata from the response
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $role,
        public readonly array $content,
        public readonly string $model,
        public readonly string $stop_reason,
        public readonly ?string $stop_sequence,
        public readonly Usage $usage,
        public readonly ?array $stop_details = null,
        public readonly ?array $container = null,
    ) {
    }
}
