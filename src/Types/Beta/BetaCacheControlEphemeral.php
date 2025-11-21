<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta cache control ephemeral
 */
class BetaCacheControlEphemeral
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
