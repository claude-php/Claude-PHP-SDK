<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

class ContainerUploadBlockParam
{
    public function __construct(
        public readonly string $type = 'container_upload',
        public readonly string $file_id = '',
    ) {
    }
}
