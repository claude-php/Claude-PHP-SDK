<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool choice any
 */
class ToolChoiceAny
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
