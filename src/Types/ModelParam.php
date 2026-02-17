<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Model parameter for API requests.
 *
 * Use one of the MODEL_* constants for known models, or pass any string
 * for custom/future model identifiers.
 *
 * Current model families (as of 2026-02-17):
 *
 * Claude 4 series (latest):
 *  - MODEL_CLAUDE_OPUS_4_6         = 'claude-opus-4-6'       (latest Opus 4)
 *  - MODEL_CLAUDE_SONNET_4_6       = 'claude-sonnet-4-6'     (latest Sonnet 4)
 *  - MODEL_CLAUDE_OPUS_4_5         = 'claude-opus-4-5-20251101'
 *  - MODEL_CLAUDE_SONNET_4_5       = 'claude-sonnet-4-5-20250929'
 *  - MODEL_CLAUDE_HAIKU_4_5        = 'claude-haiku-4-5-20251001'
 *
 * Claude 3.7 series:
 *  - MODEL_CLAUDE_3_7_SONNET_LATEST = 'claude-3-7-sonnet-latest'
 *  - MODEL_CLAUDE_3_7_SONNET        = 'claude-3-7-sonnet-20250219'
 *
 * Claude 3.5 series:
 *  - MODEL_CLAUDE_3_5_HAIKU_LATEST  = 'claude-3-5-haiku-latest'
 *  - MODEL_CLAUDE_3_5_HAIKU         = 'claude-3-5-haiku-20241022'
 *
 * Claude 3 legacy series:
 *  - MODEL_CLAUDE_3_OPUS_LATEST     = 'claude-3-opus-latest'
 *  - MODEL_CLAUDE_3_OPUS            = 'claude-3-opus-20240229'
 *  - MODEL_CLAUDE_3_HAIKU           = 'claude-3-haiku-20240307'
 *
 * @readonly
 */
class ModelParam
{
    // -------------------------------------------------------------------------
    // Claude 4 — Opus 4.6 (Feb 2026)
    // -------------------------------------------------------------------------

    /** Latest Claude Opus 4 model (recommended for most powerful tasks) */
    public const MODEL_CLAUDE_OPUS_4_6 = 'claude-opus-4-6';

    /** Latest Claude Sonnet 4 model (Feb 2026) */
    public const MODEL_CLAUDE_SONNET_4_6 = 'claude-sonnet-4-6';

    // -------------------------------------------------------------------------
    // Claude 4 — Opus / Sonnet / Haiku 4.5 (Nov 2025)
    // -------------------------------------------------------------------------

    /** Claude Opus 4.5 — state-of-the-art coding, agents, and computer use */
    public const MODEL_CLAUDE_OPUS_4_5 = 'claude-opus-4-5-20251101';

    /** Alias: claude-opus-4-5 (without date) */
    public const MODEL_CLAUDE_OPUS_4_5_ALIAS = 'claude-opus-4-5';

    /** Claude Sonnet 4.5 (Sep 2025) */
    public const MODEL_CLAUDE_SONNET_4_5 = 'claude-sonnet-4-5-20250929';

    /** Alias: claude-sonnet-4-5 (without date) */
    public const MODEL_CLAUDE_SONNET_4_5_ALIAS = 'claude-sonnet-4-5';

    /** Claude Haiku 4.5 (Oct 2025) */
    public const MODEL_CLAUDE_HAIKU_4_5 = 'claude-haiku-4-5-20251001';

    /** Alias: claude-haiku-4-5 (without date) */
    public const MODEL_CLAUDE_HAIKU_4_5_ALIAS = 'claude-haiku-4-5';

    // -------------------------------------------------------------------------
    // Claude 4 — Sonnet 4 / Opus 4.0 (May 2025)
    // -------------------------------------------------------------------------

    /** Claude Sonnet 4 (May 2025) */
    public const MODEL_CLAUDE_SONNET_4_20250514 = 'claude-sonnet-4-20250514';

    /** Alias: claude-4-sonnet-20250514 */
    public const MODEL_CLAUDE_4_SONNET_20250514 = 'claude-4-sonnet-20250514';

    /** Alias: claude-sonnet-4-0 */
    public const MODEL_CLAUDE_SONNET_4_0 = 'claude-sonnet-4-0';

    /** Claude Opus 4.0 (May 2025) */
    public const MODEL_CLAUDE_OPUS_4_20250514 = 'claude-opus-4-20250514';

    /** Alias: claude-4-opus-20250514 */
    public const MODEL_CLAUDE_4_OPUS_20250514 = 'claude-4-opus-20250514';

    /** Alias: claude-opus-4-0 */
    public const MODEL_CLAUDE_OPUS_4_0 = 'claude-opus-4-0';

    /** Claude Opus 4.1 (Aug 2025) */
    public const MODEL_CLAUDE_OPUS_4_1_20250805 = 'claude-opus-4-1-20250805';

    // -------------------------------------------------------------------------
    // Claude 3.7 (Feb 2025)
    // -------------------------------------------------------------------------

    /** Latest Claude 3.7 Sonnet (always points to the newest 3.7 Sonnet) */
    public const MODEL_CLAUDE_3_7_SONNET_LATEST = 'claude-3-7-sonnet-latest';

    /** Claude 3.7 Sonnet (Feb 2025) — first model with extended thinking */
    public const MODEL_CLAUDE_3_7_SONNET_20250219 = 'claude-3-7-sonnet-20250219';

    // -------------------------------------------------------------------------
    // Claude 3.5 (2024)
    // -------------------------------------------------------------------------

    /** Latest Claude 3.5 Haiku */
    public const MODEL_CLAUDE_3_5_HAIKU_LATEST = 'claude-3-5-haiku-latest';

    /** Claude 3.5 Haiku (Oct 2024) */
    public const MODEL_CLAUDE_3_5_HAIKU_20241022 = 'claude-3-5-haiku-20241022';

    // -------------------------------------------------------------------------
    // Claude 3 legacy (2024)
    // -------------------------------------------------------------------------

    /** Latest Claude 3 Opus */
    public const MODEL_CLAUDE_3_OPUS_LATEST = 'claude-3-opus-latest';

    /** Claude 3 Opus (Feb 2024) */
    public const MODEL_CLAUDE_3_OPUS_20240229 = 'claude-3-opus-20240229';

    /** Claude 3 Haiku (Mar 2024) */
    public const MODEL_CLAUDE_3_HAIKU_20240307 = 'claude-3-haiku-20240307';

    // -------------------------------------------------------------------------

    /**
     * @param string $model The model identifier (use a MODEL_* constant or any valid string)
     */
    public function __construct(
        public readonly string $model,
    ) {
    }
}
