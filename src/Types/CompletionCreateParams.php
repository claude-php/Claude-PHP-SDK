<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Parameters for creating a completion (legacy text completion)
 *
 * @readonly
 */
class CompletionCreateParams
{
    /**
     * @param string $model The model to use
     * @param string $prompt The prompt text
     * @param int $max_tokens_to_sample The maximum tokens to generate
     * @param array<string>|null $stop_sequences Sequences where generation stops
     * @param float|null $temperature Sampling temperature (0-1)
     * @param float|null $top_k Sample from top-k tokens
     * @param float|null $top_p Sample from top-p tokens
     * @param array<string, mixed>|null $metadata Optional metadata
     */
    public function __construct(
        public readonly string $model,
        public readonly string $prompt,
        public readonly int $max_tokens_to_sample,
        public readonly ?array $stop_sequences = null,
        public readonly ?float $temperature = null,
        public readonly ?float $top_k = null,
        public readonly ?float $top_p = null,
        public readonly ?array $metadata = null,
    ) {}
}
