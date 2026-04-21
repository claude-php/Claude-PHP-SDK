<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Input tokens trigger parameter for compaction/budget controls.
 */
class BetaInputTokensTriggerParam
{
    public function __construct(
        public readonly string $type = 'input_tokens',
        public readonly int $value = 0,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['type' => $this->type, 'value' => $this->value];
    }
}
