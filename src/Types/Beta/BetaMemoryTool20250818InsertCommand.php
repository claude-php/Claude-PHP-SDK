<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Memory tool "insert" command — insert text at a specific line.
 */
class BetaMemoryTool20250818InsertCommand
{
    /**
     * @param string $path        Path to the file where text should be inserted
     * @param int    $insert_line Line number where text should be inserted
     * @param string $insert_text Text to insert at the specified line
     * @param string $command     Always "insert"
     */
    public function __construct(
        public readonly string $path,
        public readonly int $insert_line,
        public readonly string $insert_text,
        public readonly string $command = 'insert',
    ) {
    }
}
