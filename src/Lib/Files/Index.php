<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Files;

/**
 * Files library index - provides registry and helper functions.
 */

/**
 * Get the files library components.
 *
 * @return array<string, mixed>
 */
function getFilesLibrary(): array
{
    return [
        'FilesUtils' => FilesUtils::class,
    ];
}

/**
 * Get the files library version.
 */
function getFilesLibraryVersion(): string
{
    return '1.0.0';
}
