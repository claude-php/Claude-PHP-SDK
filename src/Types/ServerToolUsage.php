<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Server tool usage
 */
class ServerToolUsage
{
    public function __construct(
        public readonly string $type,
        public readonly int $id,
    ) {}
}
