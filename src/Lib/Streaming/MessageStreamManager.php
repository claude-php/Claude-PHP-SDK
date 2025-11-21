<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Streaming;

use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\Usage;

/**
 * MessageStreamManager handles aggregation of streamed message events.
 *
 * Accumulates events from a stream and reconstructs the complete Message object.
 * Provides utilities for accessing partial results and final message state.
 */
class MessageStreamManager
{
    /**
     * @var array<string, mixed> Current message state
     */
    private array $message = [
        'id' => null,
        'type' => 'message',
        'role' => 'assistant',
        'content' => [],
        'model' => null,
        'stop_reason' => null,
        'stop_sequence' => null,
        'usage' => ['input_tokens' => 0, 'output_tokens' => 0, 'server_tool_use' => null],
    ];

    /**
     * @var array<int, mixed> Current content blocks being accumulated
     */
    private array $currentContent = [];

    /**
     * @var int Current content block index
     */
    private int $currentBlockIndex = 0;

    /**
     * Create a new MessageStreamManager.
     */
    public function __construct()
    {
    }

    /**
     * Add a streamed event to accumulate message state.
     *
     * @param array<string, mixed> $event The streamed event
     */
    public function addEvent(array $event): void
    {
        $eventType = $event['type'] ?? null;

        match ($eventType) {
            'message_start' => $this->handleMessageStart($event),
            'message_delta' => $this->handleMessageDelta($event),
            'message_stop' => $this->handleMessageStop($event),
            'content_block_start' => $this->handleContentBlockStart($event),
            'content_block_delta' => $this->handleContentBlockDelta($event),
            'content_block_stop' => $this->handleContentBlockStop($event),
            default => null,
        };
    }

    /**
     * Get the current message state.
     *
     * @return Message The accumulated message
     */
    public function getMessage(): Message
    {
        return new Message(
            id: $this->message['id'] ?? 'unknown',
            type: $this->message['type'] ?? 'message',
            role: $this->message['role'] ?? 'assistant',
            content: $this->message['content'] ?? [],
            model: $this->message['model'] ?? 'unknown',
            stop_reason: $this->message['stop_reason'],
            stop_sequence: $this->message['stop_sequence'],
            usage: new Usage(
                input_tokens: $this->message['usage']['input_tokens'] ?? 0,
                output_tokens: $this->message['usage']['output_tokens'] ?? 0,
                cache_creation_input_tokens: $this->message['usage']['cache_creation_input_tokens'] ?? null,
                cache_read_input_tokens: $this->message['usage']['cache_read_input_tokens'] ?? null,
                server_tool_use: $this->message['usage']['server_tool_use'] ?? null,
            ),
        );
    }

    /**
     * Get accumulated text content.
     *
     * @return string All text accumulated so far
     */
    public function getTextContent(): string
    {
        $text = '';
        foreach ($this->message['content'] ?? [] as $block) {
            if ('text' === $block['type']) {
                $text .= $block['text'] ?? '';
            }
        }

        return $text;
    }

    /**
     * Handle message_start event.
     */
    private function handleMessageStart(array $event): void
    {
        $message = $event['message'] ?? [];
        $this->message['id'] = $message['id'] ?? null;
        $this->message['model'] = $message['model'] ?? null;
        $this->message['role'] = $message['role'] ?? 'assistant';

        $usage = $message['usage'] ?? [];
        if (isset($usage['input_tokens'])) {
            $this->message['usage']['input_tokens'] = $usage['input_tokens'];
        }
        if (isset($usage['cache_creation_input_tokens'])) {
            $this->message['usage']['cache_creation_input_tokens'] = $usage['cache_creation_input_tokens'];
        }
        if (isset($usage['cache_read_input_tokens'])) {
            $this->message['usage']['cache_read_input_tokens'] = $usage['cache_read_input_tokens'];
        }
        if (isset($usage['server_tool_use'])) {
            $this->message['usage']['server_tool_use'] = $usage['server_tool_use'];
        }
    }

    /**
     * Handle message_delta event.
     */
    private function handleMessageDelta(array $event): void
    {
        $delta = $event['delta'] ?? [];
        if (isset($delta['stop_reason'])) {
            $this->message['stop_reason'] = $delta['stop_reason'];
        }
        if (isset($delta['stop_sequence'])) {
            $this->message['stop_sequence'] = $delta['stop_sequence'];
        }

        $usage = $event['usage'] ?? [];
        if (isset($usage['output_tokens'])) {
            $this->message['usage']['output_tokens'] = $usage['output_tokens'];
        }
        if (isset($usage['server_tool_use'])) {
            $this->message['usage']['server_tool_use'] = $usage['server_tool_use'];
        }
        if (isset($usage['input_tokens'])) {
            $this->message['usage']['input_tokens'] = $usage['input_tokens'];
        }
        if (isset($usage['cache_creation_input_tokens'])) {
            $this->message['usage']['cache_creation_input_tokens'] = $usage['cache_creation_input_tokens'];
        }
        if (isset($usage['cache_read_input_tokens'])) {
            $this->message['usage']['cache_read_input_tokens'] = $usage['cache_read_input_tokens'];
        }
    }

    /**
     * Handle message_stop event.
     */
    private function handleMessageStop(array $event): void
    {
        // Message processing complete
    }

    /**
     * Handle content_block_start event.
     */
    private function handleContentBlockStart(array $event): void
    {
        $block = $event['content_block'] ?? [];
        $this->currentBlockIndex = count($this->message['content']);
        $this->currentContent = array_merge(['type' => $block['type'] ?? 'text'], $block);

        if ('text' === $block['type']) {
            $this->currentContent['text'] = '';
        }

        $this->message['content'][] = $this->currentContent;
    }

    /**
     * Handle content_block_delta event.
     */
    private function handleContentBlockDelta(array $event): void
    {
        $delta = $event['delta'] ?? [];

        if (isset($this->message['content'][$this->currentBlockIndex])) {
            $block = &$this->message['content'][$this->currentBlockIndex];

            if ('text_delta' === $delta['type'] && isset($delta['text'])) {
                $block['text'] = ($block['text'] ?? '') . $delta['text'];
            } elseif ('input_json_delta' === $delta['type'] && isset($delta['partial_json'])) {
                $block['input'] = ($block['input'] ?? '') . $delta['partial_json'];
            }
        }
    }

    /**
     * Handle content_block_stop event.
     */
    private function handleContentBlockStop(array $event): void
    {
        // Content block complete
    }
}
