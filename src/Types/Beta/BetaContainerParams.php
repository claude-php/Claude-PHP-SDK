<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaContainerParams
{
    public function __construct(
        public readonly ?string $id = null,
    ) {
    }
}
