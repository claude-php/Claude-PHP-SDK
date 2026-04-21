<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaUnrestrictedNetworkParam
{
    public function __construct(
        public readonly string $type = 'unrestricted',
    ) {
    }
}
