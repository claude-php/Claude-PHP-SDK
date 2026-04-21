<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Anthropic-provided skill reference.
 */
class AnthropicSkill
{
    public function __construct(
        public readonly string $type = 'anthropic',
        public readonly string $name = '',
        public readonly ?string $version = null,
    ) {
    }
}
