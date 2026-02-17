<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Adaptive thinking configuration parameter.
 *
 * When type is "adaptive", the model automatically decides whether to use
 * extended thinking based on the complexity of the request. This is the
 * recommended mode for claude-opus-4-6 and newer models.
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/extended-thinking
 */
class ThinkingConfigAdaptiveParam
{
    /**
     * @param string $type Must be "adaptive"
     */
    public function __construct(
        public readonly string $type = 'adaptive',
    ) {
    }

    /**
     * Convert to array for API request.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['type' => $this->type];
    }
}
