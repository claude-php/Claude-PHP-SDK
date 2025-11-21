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
     * @param array<array<string, mixed>|MessageParam> $messages The message history
     * @param int $max_tokens The maximum tokens to generate
     * @param null|array<string, mixed> $tools Optional tools available to the model
     * @param null|array<string, mixed> $tool_choice Tool choice constraint
     * @param null|array<string> $stop_sequences Sequences where generation stops
     * @param null|array<string, mixed> $thinking Extended thinking configuration
     * @param null|array<string, mixed> $temperature Sampling temperature (if using batches)
     * @param null|array<string, mixed> $system System prompt or instructions
     * @param null|array<string, mixed> $metadata Optional metadata
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
    ) {
    }
}
