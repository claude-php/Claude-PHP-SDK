<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Streaming;

/**
 * Event types for message streaming.
 *
 * These interfaces define the structure of events that can be emitted during
 * message streaming operations.
 */

/**
 * Base event interface.
 */
interface StreamEvent
{
    /**
     * Get the event type.
     */
    public function type(): string;
}

/**
 * Text content event.
 *
 * Emitted when text content is received in the stream.
 */
interface TextEvent extends StreamEvent
{
    /**
     * Get the text content.
     */
    public function text(): string;
}

/**
 * Input JSON event (tool use).
 *
 * Emitted when tool use input is being streamed.
 */
interface InputJsonEvent extends StreamEvent
{
    /**
     * Get the partial JSON input.
     */
    public function partialJson(): string;
}

/**
 * Message stop event.
 *
 * Emitted when the message stream ends.
 */
interface MessageStopEvent extends StreamEvent
{
    /**
     * Get the stop reason.
     */
    public function stopReason(): string;
}

/**
 * Content block stop event.
 *
 * Emitted when a content block (text, tool use, etc.) completes.
 */
interface ContentBlockStopEvent extends StreamEvent
{
}

/**
 * Beta parsed text event.
 *
 * Beta variant for structured outputs with parsed text.
 */
interface ParsedBetaTextEvent extends StreamEvent
{
    /**
     * Get parsed output (for structured outputs).
     */
    public function parsedOutput(): mixed;

    /**
     * Get text content.
     */
    public function text(): string;
}

/**
 * Beta parsed message stop event.
 *
 * Beta variant for message stop with additional metadata.
 */
interface ParsedBetaMessageStopEvent extends StreamEvent
{
}

/**
 * Beta parsed content block stop event.
 *
 * Beta variant for content block stop.
 */
interface ParsedBetaContentBlockStopEvent extends StreamEvent
{
}

/**
 * Beta input JSON event.
 *
 * Beta variant for tool use input.
 */
interface BetaInputJsonEvent extends StreamEvent
{
    /**
     * Get partial JSON input.
     */
    public function partialJson(): string;
}
