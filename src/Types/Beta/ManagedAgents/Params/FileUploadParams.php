<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

class FileUploadParams
{
    /**
     * @param string|resource $file File contents or resource handle
     * @param string|null $filename Optional filename
     * @param string|null $purpose Purpose of the file (e.g. "skill", "session")
     */
    public function __construct(
        public readonly mixed $file,
        public readonly ?string $filename = null,
        public readonly ?string $purpose = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'file' => $this->file,
            'filename' => $this->filename,
            'purpose' => $this->purpose,
        ], static fn ($v) => null !== $v);
    }
}
