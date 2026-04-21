<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Managed agents model identifier.
 */
class Model
{
    public function __construct(
        public readonly string $model = '',
    ) {
    }
}
