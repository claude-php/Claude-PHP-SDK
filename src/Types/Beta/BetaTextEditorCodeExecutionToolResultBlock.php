<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Wrapper around text editor code execution result variants.
 */
class BetaTextEditorCodeExecutionToolResultBlock
{
    public function __construct(
        public readonly string $type = 'text_editor_code_execution_tool_result',
        public readonly ?string $tool_use_id = null,
        public readonly ?array $content = null,
        public readonly ?bool $is_error = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'text_editor_code_execution_tool_result',
            tool_use_id: $data['tool_use_id'] ?? null,
            content: $data['content'] ?? null,
            is_error: $data['is_error'] ?? null,
        );
    }
}
