<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta file metadata
 */
class FileMetadata
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $filename,
        public readonly int $size,
        public readonly string $created_at,
    ) {}
}
