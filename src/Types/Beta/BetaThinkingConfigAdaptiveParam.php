<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta adaptive thinking configuration parameter.
 *
 * When type is "adaptive", the model automatically decides whether to use
 * extended thinking based on the complexity of the request. This is the
 * recommended mode for claude-opus-4-6 and newer models.
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/extended-thinking
 */
class BetaThinkingConfigAdaptiveParam
{
    /**
     * @param string $type Must be "adaptive"
     * @param string|null $display Display mode: "summarized" or "omitted"
     */
    public function __construct(
        public readonly string $type = 'adaptive',
        public readonly ?string $display = null,
    ) {
    }

    /**
     * Convert to array for API request.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'display' => $this->display,
        ], static fn ($v) => null !== $v);
    }
}
