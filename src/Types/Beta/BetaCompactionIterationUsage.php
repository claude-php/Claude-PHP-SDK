<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaCompactionIterationUsage
{
    public function __construct(
        public readonly int $input_tokens = 0,
        public readonly int $output_tokens = 0,
    ) {
    }
}
