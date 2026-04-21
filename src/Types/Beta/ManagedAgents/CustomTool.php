<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Custom tool definition for managed agents.
 */
class CustomTool
{
    public function __construct(
        public readonly string $name = '',
        public readonly ?string $description = null,
        public readonly ?array $input_schema = null,
    ) {
    }
}
