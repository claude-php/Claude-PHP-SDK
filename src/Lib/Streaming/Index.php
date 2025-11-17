<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Streaming;

/**
 * Index of streaming library exports.
 *
 * This module provides streaming support for the Claude API, including
 * message stream managers and event types.
 */

// Export all classes and interfaces
use ClaudePhp\Lib\Streaming\Types;
use ClaudePhp\Lib\Streaming\MessageStream;
use ClaudePhp\Lib\Streaming\AsyncMessageStream;
use ClaudePhp\Lib\Streaming\MessageStreamManager;
use ClaudePhp\Lib\Streaming\AsyncMessageStreamManager;
use ClaudePhp\Lib\Streaming\StructuredOutputStream;

/**
 * Get the streaming library.
 *
 * @return array<string, string> Registry of streaming components
 */
function getStreamingLibrary(): array
{
    return [
        'MessageStream' => MessageStream::class,
        'AsyncMessageStream' => AsyncMessageStream::class,
        'MessageStreamManager' => MessageStreamManager::class,
        'AsyncMessageStreamManager' => AsyncMessageStreamManager::class,
        'StructuredOutputStream' => StructuredOutputStream::class,
    ];
}
