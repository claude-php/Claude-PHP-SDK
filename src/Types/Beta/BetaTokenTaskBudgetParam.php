<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Token task budget parameter for output config.
 */
class BetaTokenTaskBudgetParam
{
    public function __construct(
        public readonly string $type = 'tokens',
        public readonly ?int $total = null,
        public readonly ?int $remaining = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'total' => $this->total,
            'remaining' => $this->remaining,
        ], static fn ($v) => null !== $v);
    }
}
