<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Vertex;

/**
 * Vertex library index - provides registry and helper functions.
 */

/**
 * Get the vertex library components.
 *
 * @return array<string, mixed>
 */
function getVertexLibrary(): array
{
    return [
        'AnthropicVertex' => AnthropicVertex::class,
        'AsyncAnthropicVertex' => AsyncAnthropicVertex::class,
    ];
}

/**
 * Get the vertex library version.
 */
function getVertexLibraryVersion(): string
{
    return '1.0.0';
}
