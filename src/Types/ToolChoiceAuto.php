<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool choice auto
 */
class ToolChoiceAuto
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
