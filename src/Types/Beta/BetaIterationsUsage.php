<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaIterationsUsage
{
    /**
     * @param array<int, BetaMessageIterationUsage> $iterations
     */
    public function __construct(
        public readonly array $iterations = [],
    ) {
    }
}
