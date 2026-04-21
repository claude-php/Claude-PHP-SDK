<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Managed Agents model identifier.
 *
 * Use one of the MODEL_* constants for known managed-agents-supported
 * models, or pass any string for custom/future model identifiers.
 *
 * Mirrors Python `BetaManagedAgentsModel`
 * (`src/anthropic/types/beta/beta_managed_agents_model.py`).
 *
 * @readonly
 */
class Model
{
    /** Latest Claude Opus 4 model (Apr 2026) */
    public const MODEL_CLAUDE_OPUS_4_7 = 'claude-opus-4-7';

    /** Claude Opus 4.6 (Feb 2026) */
    public const MODEL_CLAUDE_OPUS_4_6 = 'claude-opus-4-6';

    /** Claude Sonnet 4.6 (Feb 2026) */
    public const MODEL_CLAUDE_SONNET_4_6 = 'claude-sonnet-4-6';

    /** Alias: Claude Haiku 4.5 (without date) */
    public const MODEL_CLAUDE_HAIKU_4_5 = 'claude-haiku-4-5';

    /** Claude Haiku 4.5 (Oct 2025) */
    public const MODEL_CLAUDE_HAIKU_4_5_20251001 = 'claude-haiku-4-5-20251001';

    /** Alias: Claude Opus 4.5 (without date) */
    public const MODEL_CLAUDE_OPUS_4_5 = 'claude-opus-4-5';

    /** Claude Opus 4.5 (Nov 2025) */
    public const MODEL_CLAUDE_OPUS_4_5_20251101 = 'claude-opus-4-5-20251101';

    /** Alias: Claude Sonnet 4.5 (without date) */
    public const MODEL_CLAUDE_SONNET_4_5 = 'claude-sonnet-4-5';

    /** Claude Sonnet 4.5 (Sep 2025) */
    public const MODEL_CLAUDE_SONNET_4_5_20250929 = 'claude-sonnet-4-5-20250929';

    /**
     * @param string $model The managed-agents model identifier
     *                       (use a MODEL_* constant or any valid string)
     */
    public function __construct(
        public readonly string $model = self::MODEL_CLAUDE_OPUS_4_7,
    ) {
    }
}
