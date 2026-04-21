<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\Usage;

/**
 * Beta message response with structured output already parsed.
 *
 * Mirrors Python `parsed_beta_message.py`.
 */
class BetaParsedMessage extends Message
{
    public function __construct(
        string $id,
        string $type,
        string $role,
        array $content,
        string $model,
        ?string $stop_reason,
        ?string $stop_sequence,
        ?Usage $usage,
        public readonly mixed $parsed = null,
        ?array $stop_details = null,
        ?array $container = null,
    ) {
        parent::__construct(
            id: $id,
            type: $type,
            role: $role,
            content: $content,
            model: $model,
            stop_reason: $stop_reason,
            stop_sequence: $stop_sequence,
            usage: $usage,
            stop_details: $stop_details,
            container: $container,
        );
    }
}
