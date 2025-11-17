<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta citations delta
 */
class BetaCitationsDelta
{
    public function __construct(
        public readonly string $type,
        public readonly array $citations,
    ) {}
}
