<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Parameter type for advisor redacted result blocks.
 */
class BetaAdvisorRedactedResultBlockParam
{
    public function __construct(
        public readonly string $type = 'advisor_redacted_result',
        public readonly ?string $id = null,
    ) {
    }
}
