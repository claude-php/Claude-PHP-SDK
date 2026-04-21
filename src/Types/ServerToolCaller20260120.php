<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Server tool caller (Jan 2026 variant).
 *
 * Mirrors Python `server_tool_caller_20260120.py`.
 */
class ServerToolCaller20260120
{
    public function __construct(
        public readonly string $type = 'server_20260120',
    ) {
    }
}
