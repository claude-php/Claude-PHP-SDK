<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaModelCapabilities
{
    public function __construct(
        public readonly ?BetaThinkingCapability $thinking = null,
        public readonly ?BetaEffortCapability $effort = null,
        public readonly ?BetaContextManagementCapability $context_management = null,
    ) {
    }
}
