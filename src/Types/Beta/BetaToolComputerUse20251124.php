<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

use ClaudePhp\Types\CacheControlEphemeral;

/**
 * Beta computer use tool (2025-11-24 version / v5)
 *
 * Enable Claude to interact with desktop environments.
 * Supports mouse, keyboard, screenshots, and zoom features.
 */
class BetaToolComputerUse20251124
{
    /**
     * @param string $type The tool type (computer_20251124)
     * @param string $name Name of the tool (computer)
     * @param int $display_width_px The width of the display in pixels
     * @param int $display_height_px The height of the display in pixels
     * @param null|array<string> $allowed_callers List of allowed callers (direct, code_execution_20250825)
     * @param null|CacheControlEphemeral $cache_control Cache control configuration
     * @param null|bool $defer_loading If true, tool will not be included in initial system prompt
     * @param null|int $display_number The X11 display number
     * @param null|bool $enable_zoom Whether to enable zoomed-in screenshot action
     * @param null|array<array<string, mixed>> $input_examples Example inputs for the tool
     * @param null|bool $strict Whether to use strict mode
     */
    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly int $display_width_px,
        public readonly int $display_height_px,
        public readonly ?array $allowed_callers = null,
        public readonly ?CacheControlEphemeral $cache_control = null,
        public readonly ?bool $defer_loading = null,
        public readonly ?int $display_number = null,
        public readonly ?bool $enable_zoom = null,
        public readonly ?array $input_examples = null,
        public readonly ?bool $strict = null,
    ) {
    }
}
