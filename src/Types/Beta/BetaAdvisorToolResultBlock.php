<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Tool result block wrapping an advisor invocation result.
 */
class BetaAdvisorToolResultBlock
{
    public function __construct(
        public readonly string $type = 'advisor_tool_result',
        public readonly ?string $id = null,
        public readonly ?string $tool_use_id = null,
        public readonly ?array $content = null,
        public readonly ?bool $is_error = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'advisor_tool_result',
            id: $data['id'] ?? null,
            tool_use_id: $data['tool_use_id'] ?? null,
            content: $data['content'] ?? null,
            is_error: $data['is_error'] ?? null,
        );
    }
}
