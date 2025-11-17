<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta gateway timeout error
 */
class BetaGatewayTimeoutError
{
    public function __construct(
        public readonly string $type,
        public readonly string $message,
    ) {}
}
