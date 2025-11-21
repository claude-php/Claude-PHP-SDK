<?php

declare(strict_types=1);

namespace ClaudePhp\Lib;

/**
 * Main library index - provides access to all lib modules.
 *
 * This module provides high-level abstractions and utilities for working
 * with the Claude API, including streaming, tool use, cloud integrations,
 * and more.
 */

/**
 * Get all available lib modules.
 *
 * @return array<string, array<string, mixed>>
 */
function getAllLibraries(): array
{
    return [
        'streaming' => Streaming\getStreamingLibrary(),
        'tools' => Tools\getToolsLibrary(),
        'parse' => Parse\getParseLibrary(),
        'files' => Files\getFilesLibrary(),
        'extras' => Extras\getExtrasLibrary(),
        'bedrock' => Bedrock\getBedrockLibrary(),
        'vertex' => Vertex\getVertexLibrary(),
        'foundry' => Foundry\getFoundryLibrary(),
    ];
}

/**
 * Get library versions.
 *
 * @return array<string, string>
 */
function getLibraryVersions(): array
{
    return [
        'streaming' => '1.0.0',
        'tools' => '1.0.0',
        'parse' => '1.0.0',
        'files' => '1.0.0',
        'extras' => '1.0.0',
        'bedrock' => '1.0.0',
        'vertex' => '1.0.0',
        'foundry' => '1.0.0',
    ];
}

/**
 * Get main library version.
 */
function getLibVersion(): string
{
    return '1.0.0';
}
