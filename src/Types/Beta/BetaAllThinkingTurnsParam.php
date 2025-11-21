<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta all thinking turns parameter
 *
 * @readonly
 */
class BetaAllThinkingTurnsParam
{
    /**
     * @param string $type Parameter type ("all_thinking_turns")
     * @param bool $include_all Whether to include all thinking turns
     * @param null|int $max_turns Optional maximum number of thinking turns
     */
    public function __construct(
        public readonly string $type,
        public readonly bool $include_all,
        public readonly ?int $max_turns = null,
    ) {
    }
}
