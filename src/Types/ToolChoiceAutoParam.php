<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for ToolChoiceAuto
 *
 * @readonly
 */
class ToolChoiceAutoParam
{
    /**
     * @param string $type The type identifier ("auto")
     */
    public function __construct(
        public readonly string $type,
    ) {
    }
}
