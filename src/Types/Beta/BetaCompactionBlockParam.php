<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaCompactionBlockParam
{
    public function __construct(
        public readonly string $type = 'compaction',
        public readonly ?string $summary = null,
    ) {
    }
}
