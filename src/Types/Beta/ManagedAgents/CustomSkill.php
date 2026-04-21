<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Custom skill definition for managed agents.
 */
class CustomSkill
{
    public function __construct(
        public readonly string $name = '',
        public readonly ?string $description = null,
        public readonly ?string $skill_id = null,
        public readonly ?int $version = null,
    ) {
    }
}
