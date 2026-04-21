<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Parameter type for advisor tool result errors.
 */
class BetaAdvisorToolResultErrorParam
{
    public function __construct(
        public readonly string $type = 'advisor_error',
        public readonly ?string $error_code = null,
        public readonly ?string $message = null,
    ) {
    }
}
