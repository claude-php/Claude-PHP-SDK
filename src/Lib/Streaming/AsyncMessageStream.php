<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Streaming;

use ClaudePhp\Responses\Message;

/**
 * AsyncMessageStream handles asynchronous streaming of messages.
 *
 * Async variant that works with async event streams and generators.
 * Provides the same convenience methods as MessageStream.
 */
class AsyncMessageStream
{
    /**
     * @var mixed The underlying async event stream/generator
     */
    private mixed $eventStream;

    /**
     * @var Message|null The accumulated final message
     */
    private ?Message $finalMessage = null;

    /**
     * @var string The accumulated text content
     */
    private string $textAccumulator = '';

    /**
     * Create a new AsyncMessageStream.
     *
     * @param mixed $eventStream The underlying async event stream
     */
    public function __construct(mixed $eventStream)
    {
        $this->eventStream = $eventStream;
    }

    /**
     * Get the final accumulated message.
     *
     * @return Message|null The final message if available
     */
    public function finalMessage(): ?Message
    {
        return $this->finalMessage;
    }

    /**
     * Get the accumulated text content.
     *
     * @return string The accumulated text
     */
    public function textStream(): string
    {
        return $this->textAccumulator;
    }

    /**
     * Consume the entire stream and return the final message.
     *
     * @return Message The final message
     */
    public function getFinalMessage(): Message
    {
        if ($this->finalMessage === null) {
            throw new \RuntimeException('Stream ended without producing a final message');
        }

        return $this->finalMessage;
    }

    /**
     * Process an event and update internal state.
     *
     * @param mixed $event The event to process
     */
    private function processEvent(mixed $event): void
    {
        if ($event instanceof TextEvent) {
            $this->textAccumulator .= $event->text();
        } elseif ($event instanceof MessageStopEvent) {
            // Message stream ended
        }
    }
}
