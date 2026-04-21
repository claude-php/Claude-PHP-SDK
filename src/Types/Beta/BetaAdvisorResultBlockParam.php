<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Parameter type for advisor result blocks.
 */
class BetaAdvisorResultBlockParam
{
    public function __construct(
        public readonly string $type = 'advisor_result',
        public readonly ?string $id = null,
        public readonly ?array $content = null,
    ) {
    }
}
