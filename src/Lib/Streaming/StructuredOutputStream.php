<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Streaming;

use ClaudePhp\Lib\Parse\ResponseParser;
use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\StreamResponse;

/**
 * Streaming wrapper that augments events with parsed structured output snapshots.
 *
 * Wraps a {@see MessageStream} and attempts to parse accumulated text content
 * for each content block according to the provided JSON schema.
 */
class StructuredOutputStream implements \IteratorAggregate
{
    private MessageStream $messageStream;

    /**
     * @var array<int, string> Accumulated text per content block index
     */
    private array $buffers = [];

    /**
     * @param array<string, mixed> $schema JSON schema describing the structured output
     */
    public function __construct(
        StreamResponse $response,
        private readonly array $schema
    ) {
        $this->messageStream = new MessageStream($response);
    }

    /**
     * Iterate over the underlying message stream while injecting parsed snapshots.
     *
     * @return \Traversable<int, array<string, mixed>>
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->messageStream as $event) {
            if (($event['type'] ?? null) === 'content_block_delta' && ($event['delta']['type'] ?? null) === 'text_delta') {
                $index = $event['index'] ?? 0;
                $text = (string) ($event['delta']['text'] ?? '');
                $this->buffers[$index] = ($this->buffers[$index] ?? '') . $text;

                $parsed = ResponseParser::tryParseText($this->buffers[$index], $this->schema);
                if ($parsed !== null) {
                    $event['parsed_output'] = $parsed;
                }
            }

            yield $event;
        }
    }

    /**
     * Retrieve the final accumulated message once the stream completes.
     */
    public function getFinalMessage(): Message
    {
        return $this->messageStream->getFinalMessage();
    }
}
