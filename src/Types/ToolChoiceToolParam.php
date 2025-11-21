<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for ToolChoiceTool
 *
 * @readonly
 */
class ToolChoiceToolParam
{
    /**
     * @param string $type The type identifier ("tool")
     * @param string $name The name of the tool to force
     */
    public function __construct(
        public readonly string $type,
        public readonly string $name,
    ) {
    }
}
