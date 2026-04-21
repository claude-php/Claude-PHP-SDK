<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Parameter type for advisor tool result blocks.
 */
class BetaAdvisorToolResultBlockParam
{
    public function __construct(
        public readonly string $type = 'advisor_tool_result',
        public readonly ?string $tool_use_id = null,
        public readonly ?array $content = null,
        public readonly ?bool $is_error = null,
    ) {
    }
}
