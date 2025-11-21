<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for ToolChoiceNone
 *
 * @readonly
 */
class ToolChoiceNoneParam
{
    /**
     * @param string $type The type identifier ("none")
     */
    public function __construct(
        public readonly string $type,
    ) {
    }
}
