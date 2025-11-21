<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Parse;

/**
 * Parse library index - provides registry and helper functions.
 */

/**
 * Get the parse library components.
 *
 * @return array<string, mixed>
 */
function getParseLibrary(): array
{
    return [
        'ResponseParser' => ResponseParser::class,
        'SchemaTransformer' => SchemaTransformer::class,
    ];
}

/**
 * Get the parse library version.
 */
function getParseLibraryVersion(): string
{
    return '1.0.0';
}
