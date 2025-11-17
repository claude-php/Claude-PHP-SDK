<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Extras;

/**
 * Extras library index - provides registry and helper functions.
 */

/**
 * Get the extras library components.
 *
 * @return array<string, mixed>
 */
function getExtrasLibrary(): array
{
    return [
        'BatchUtils' => BatchUtils::class,
    ];
}

/**
 * Get the extras library version.
 *
 * @return string
 */
function getExtrasLibraryVersion(): string
{
    return '1.0.0';
}
