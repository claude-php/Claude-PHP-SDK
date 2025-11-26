<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Compaction Control param
 *
 * Configuration for automatic context compaction.
 * When enabled, the message history will be automatically summarized
 * and compressed when it exceeds the specified token threshold.
 */
class CompactionControlParam
{
    /**
     * @param bool $enabled Whether compaction is enabled
     * @param null|int $context_token_threshold Token threshold to trigger compaction (default: 100000)
     * @param null|string $model Model to use for generating the compaction summary
     * @param null|string $summary_prompt Custom prompt for generating the summary
     */
    public function __construct(
        public readonly bool $enabled,
        public readonly ?int $context_token_threshold = null,
        public readonly ?string $model = null,
        public readonly ?string $summary_prompt = null,
    ) {
    }
}
