<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Usage information for an advisor message iteration.
 */
class BetaAdvisorMessageIterationUsage
{
    public function __construct(
        public readonly int $input_tokens = 0,
        public readonly int $output_tokens = 0,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            input_tokens: $data['input_tokens'] ?? 0,
            output_tokens: $data['output_tokens'] ?? 0,
        );
    }
}
