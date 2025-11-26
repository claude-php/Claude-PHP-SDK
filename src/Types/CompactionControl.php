<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Compaction Control
 *
 * Configuration for automatic context compaction.
 * When enabled, the message history will be automatically summarized
 * and compressed when it exceeds the specified token threshold.
 */
class CompactionControl
{
    public const DEFAULT_THRESHOLD = 100000;

    public const DEFAULT_SUMMARY_PROMPT = <<<'PROMPT'
        You have been working on the task described above but have not yet completed it. Write a continuation summary that will allow you (or another instance of yourself) to resume work efficiently in a future context window where the conversation history will be replaced with this summary. Your summary should be structured, concise, and actionable. Include:
        1. Task Overview
        The user's core request and success criteria
        Any clarifications or constraints they specified
        2. Current State
        What has been completed so far
        Files created, modified, or analyzed (with paths if relevant)
        Key outputs or artifacts produced
        3. Important Discoveries
        Technical constraints or requirements uncovered
        Decisions made and their rationale
        Errors encountered and how they were resolved
        What approaches were tried that didn't work (and why)
        4. Next Steps
        Specific actions needed to complete the task
        Any blockers or open questions to resolve
        Priority order if multiple steps remain
        5. Context to Preserve
        User preferences or style requirements
        Domain-specific details that aren't obvious
        Any promises made to the user
        Be concise but complete—err on the side of including information that would prevent duplicate work or repeated mistakes. Write in a way that enables immediate resumption of the task.
        Wrap your summary in <summary></summary> tags.
        PROMPT;

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
