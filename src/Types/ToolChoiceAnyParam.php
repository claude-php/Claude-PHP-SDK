<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for ToolChoiceAny
 *
 * @readonly
 */
class ToolChoiceAnyParam
{
    /**
     * @param string $type The type identifier ("any")
     */
    public function __construct(
        public readonly string $type,
    ) {}
}
