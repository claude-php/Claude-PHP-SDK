<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Parameters for creating a message
 *
 * @readonly
 */
class MessageCreateParams
{
    /**
     * @param string $model The model to use for the message
     * @param array<MessageParam|array<string, mixed>> $messages The message history
     * @param int $max_tokens The maximum tokens to generate
     * @param array<string, mixed>|null $tools Optional tools available to the model
     * @param array<string, mixed>|null $tool_choice Tool choice constraint
     * @param array<string>|null $stop_sequences Sequences where generation stops
     * @param array<string, mixed>|null $thinking Extended thinking configuration
     * @param array<string, mixed>|null $temperature Sampling temperature (if using batches)
     * @param array<string, mixed>|null $system System prompt or instructions
     * @param array<string, mixed>|null $metadata Optional metadata
     */
    public function __construct(
        public readonly string $model,
        public readonly array $messages,
        public readonly int $max_tokens,
        public readonly ?array $tools = null,
        public readonly ?array $tool_choice = null,
        public readonly ?array $stop_sequences = null,
        public readonly ?array $thinking = null,
        public readonly ?array $temperature = null,
        public readonly ?array $system = null,
        public readonly ?array $metadata = null,
    ) {}
}
