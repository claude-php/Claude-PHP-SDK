<?php

declare(strict_types=1);

namespace ClaudePhp\Responses\Helpers;

/**
 * Helpers for inspecting streaming SSE event payloads.
 */
final class StreamEventHelper
{
    private function __construct()
    {
    }

    /**
     * Return the high-level type discriminator for an event.
     *
     * @param array<string, mixed> $event
     */
    public static function type(array $event): string
    {
        return (string) ($event['type'] ?? 'unknown');
    }

    /**
     * Determine if the event represents a text delta.
     *
     * @param array<string, mixed> $event
     */
    public static function isTextDelta(array $event): bool
    {
        return 'content_block_delta' === self::type($event)
            && ($event['delta']['type'] ?? null) === 'text_delta';
    }

    /**
     * Extract text from a text delta event.
     *
     * @param array<string, mixed> $event
     */
    public static function textDelta(array $event): ?string
    {
        if (!self::isTextDelta($event)) {
            return null;
        }

        return isset($event['delta']['text']) ? (string) $event['delta']['text'] : '';
    }

    /**
     * Determine if the event represents an input JSON delta (tool use).
     *
     * @param array<string, mixed> $event
     */
    public static function isInputJsonDelta(array $event): bool
    {
        return 'content_block_delta' === self::type($event)
            && ($event['delta']['type'] ?? null) === 'input_json_delta';
    }

    /**
     * Extract the partial JSON payload for tool use input.
     *
     * @param array<string, mixed> $event
     */
    public static function inputJsonDelta(array $event): ?string
    {
        if (!self::isInputJsonDelta($event)) {
            return null;
        }

        return isset($event['delta']['partial_json']) ? (string) $event['delta']['partial_json'] : '';
    }

    /**
     * Determine if the event marks the end of the message stream.
     *
     * @param array<string, mixed> $event
     */
    public static function isMessageStop(array $event): bool
    {
        return 'message_stop' === self::type($event);
    }
}
