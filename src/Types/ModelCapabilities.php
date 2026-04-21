<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

class ModelCapabilities
{
    public function __construct(
        public readonly ?ThinkingCapability $thinking = null,
        public readonly ?EffortCapability $effort = null,
        public readonly ?ContextManagementCapability $context_management = null,
    ) {
    }
}
