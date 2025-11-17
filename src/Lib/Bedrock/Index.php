<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Bedrock;

/**
 * Bedrock library index - provides registry and helper functions.
 */

/**
 * Get the bedrock library components.
 *
 * @return array<string, mixed>
 */
function getBedrockLibrary(): array
{
    return [
        'AnthropicBedrock' => AnthropicBedrock::class,
        'AsyncAnthropicBedrock' => AsyncAnthropicBedrock::class,
    ];
}

/**
 * Get the bedrock library version.
 *
 * @return string
 */
function getBedrockLibraryVersion(): string
{
    return '1.0.0';
}
