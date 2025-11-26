<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta Server Tool Caller
 *
 * Represents a server tool caller (e.g., code execution).
 */
class BetaServerToolCaller
{
    /**
     * @param string $type The caller type (code_execution_20250825)
     * @param string $tool_id The tool ID
     */
    public function __construct(
        public readonly string $type,
        public readonly string $tool_id,
    ) {
    }
}
