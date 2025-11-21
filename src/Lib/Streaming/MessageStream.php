<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Streaming;

use ArrayIterator;
use ClaudePhp\Responses\Message;

/**
 * MessageStream handles synchronous streaming of messages.
 *
 * Iterates over raw streaming events (arrays decoded from SSE) and provides
 * convenience helpers for accessing accumulated text and the final message.
 */
class MessageStream implements \Iterator
{
    private \Iterator $eventStream;
    private ?array $currentEvent = null;
    private int $position = 0;
    private bool $hasStarted = false;
    private string $textAccumulator = '';
    private ?Message $finalMessage = null;
    private MessageStreamManager $manager;

    /**
     * @param iterable<int, array<string, mixed>> $eventStream
     */
    public function __construct(iterable $eventStream)
    {
        $this->eventStream = $this->wrapIterator($eventStream);
        $this->manager = new MessageStreamManager();
    }

    /**
     * Get the final accumulated message, if available.
     */
    public function finalMessage(): ?Message
    {
        return $this->finalMessage;
    }

    /**
     * Get the accumulated text content from the stream.
     */
    public function textStream(): string
    {
        return $this->textAccumulator;
    }

    /**
     * Consume any remaining events and return the final message.
     */
    public function getFinalMessage(): Message
    {
        while ($this->eventStream->valid()) {
            $this->advance();
        }

        if (null === $this->finalMessage) {
            $this->finalMessage = $this->manager->getMessage();
        }

        return $this->finalMessage;
    }

    public function current(): mixed
    {
        return $this->currentEvent;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (!$this->eventStream->valid()) {
            $this->currentEvent = null;

            return;
        }

        $this->advance();
        ++$this->position;
    }

    public function valid(): bool
    {
        return null !== $this->currentEvent;
    }

    public function rewind(): void
    {
        if ($this->hasStarted) {
            throw new \LogicException('MessageStream cannot be rewound once iteration has started.');
        }

        $this->hasStarted = true;

        if (method_exists($this->eventStream, 'rewind')) {
            $this->eventStream->rewind();
        }

        if ($this->eventStream->valid()) {
            $this->advance();
        }
    }

    /**
     * Convert the provided iterable into an iterator.
     */
    private function wrapIterator(iterable $stream): \Iterator
    {
        if ($stream instanceof \Iterator) {
            return $stream;
        }

        if ($stream instanceof \IteratorAggregate) {
            return $stream->getIterator();
        }

        if (\is_array($stream)) {
            return new ArrayIterator($stream);
        }

        return new ArrayIterator(iterator_to_array($stream));
    }

    /**
     * Advance the iterator and process the current event.
     */
    private function advance(): void
    {
        $event = $this->eventStream->current();
        $this->currentEvent = $event;

        if (\is_array($event)) {
            $this->manager->addEvent($event);
            $this->accumulateText($event);

            if (($event['type'] ?? null) === 'message_stop') {
                $this->finalMessage = $this->manager->getMessage();
            }
        }

        $this->eventStream->next();

        if (!$this->eventStream->valid() && null === $this->finalMessage) {
            $this->finalMessage = $this->manager->getMessage();
        }
    }

    /**
     * Accumulate text content from content_block_delta events.
     *
     * @param array<string, mixed> $event
     */
    private function accumulateText(array $event): void
    {
        if (($event['type'] ?? null) !== 'content_block_delta') {
            return;
        }

        $delta = $event['delta'] ?? [];
        if (($delta['type'] ?? null) === 'text_delta' && isset($delta['text'])) {
            $this->textAccumulator .= (string) $delta['text'];
        }
    }
}
