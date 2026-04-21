<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta cloud configuration parameters.
 */
class BetaCloudConfigParams
{
    public function __construct(
        public readonly ?string $provider = null,
        public readonly ?string $region = null,
        public readonly ?array $resources = null,
    ) {
    }
}
