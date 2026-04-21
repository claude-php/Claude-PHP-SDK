<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Model configuration for managed agents.
 */
class ModelConfig
{
    public function __construct(
        public readonly ?string $model = null,
        public readonly ?float $temperature = null,
        public readonly ?int $max_tokens = null,
        public readonly ?array $thinking = null,
        public readonly ?array $output_config = null,
    ) {
    }
}
