<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Memory tool "create" command — create a new file.
 */
class BetaMemoryTool20250818CreateCommand
{
    /**
     * @param string $path      Path where the file should be created
     * @param string $file_text Content to write to the file
     * @param string $command   Always "create"
     */
    public function __construct(
        public readonly string $path,
        public readonly string $file_text,
        public readonly string $command = 'create',
    ) {
    }
}
