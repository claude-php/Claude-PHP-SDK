<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Tools;

/**
 * Tools library index - provides registry and helper functions.
 */

/**
 * Get the tools library components.
 *
 * @return array<string, mixed>
 */
function getToolsLibrary(): array
{
    return [
        'ToolRunner' => ToolRunner::class,
        'AsyncToolRunner' => AsyncToolRunner::class,
        'StreamingToolRunner' => StreamingToolRunner::class,
        'AsyncStreamingToolRunner' => AsyncStreamingToolRunner::class,
        'ToolUtils' => ToolUtils::class,
        'BetaToolRunner' => BetaToolRunner::class,
        'BetaToolDefinition' => BetaToolDefinition::class,
    ];
}

/**
 * Get the tools library version.
 */
function getToolsLibraryVersion(): string
{
    return '1.0.0';
}
