<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class FileResource
{
    public function __construct(
        public readonly string $type = 'file',
        public readonly string $file_id = '',
        public readonly ?string $mount_path = null,
    ) {
    }
}
