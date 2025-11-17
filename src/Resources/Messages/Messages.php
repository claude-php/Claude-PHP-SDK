<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Messages;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Types\Message;
use ClaudePhp\Types\MessageTokensCount;
use ClaudePhp\Types\Usage;
use ClaudePhp\Utils\Transform;
use ClaudePhp\Utils\FileExtraction;
use ClaudePhp\Responses\StreamResponse;

/**
 * Messages resource for the Claude API.
 *
 * Provides methods for creating messages, streaming messages, and counting tokens.
 * This is the primary API for interacting with Claude models.
 */
class Messages extends Resource
{
    /**
     * Lazy-load batches sub-resource.
     *
     * @return Batches
     */
    public function batches(): Batches
    {
        return new Batches($this->client);
    }

    /**
     * Create a message.
     *
     * Sends a request to create a message and returns the response.
     * Supports both streaming and non-streaming modes.
     *
     * @param array<string, mixed> $params Message creation parameters:
     *   - model: string (required) - Model to use (e.g., 'claude-opus-4-1-20250805')
     *   - max_tokens: int (required) - Maximum tokens to generate
     *   - messages: array (required) - Array of message objects with role and content
     *   - system: string|array (optional) - System prompt
     *   - temperature: float (optional) - Temperature (0.0-1.0)
     *   - top_p: float (optional) - Top P sampling parameter
     *   - top_k: int (optional) - Top K sampling parameter
     *   - stop_sequences: array (optional) - Custom stop sequences
     *   - tools: array (optional) - Tool definitions for tool use
     *   - tool_choice: string|array (optional) - Tool selection strategy
     *   - thinking: array (optional) - Extended thinking configuration
     *   - stream: bool (optional) - Whether to stream the response
     *   - metadata: array (optional) - Request metadata
     *   - service_tier: string (optional) - Service tier (auto, standard_only)
     *
     * @return Message|StreamResponse The message response, or stream if stream=true
     */
    public function create(array $params = []): Message|StreamResponse
    {
        // Validate required parameters
        $required = ['model', 'max_tokens', 'messages'];
        foreach ($required as $key) {
            if (!isset($params[$key])) {
                throw new \InvalidArgumentException("Missing required parameter: {$key}");
            }
        }

        // Transform request parameters
        $body = Transform::transform($params, $this->getParamTypes());

        // Extract files for multipart if present
        $files = FileExtraction::extractFiles($body, [['files', '<array>']]);

        // Determine endpoint path
        $path = '/messages';

        // Make the request
        if (!empty($params['stream'])) {
            return $this->_postStream($path, $body);
        }

        $response = $this->_post($path, $body);
        return $this->_createMessageFromArray($response);
    }

    /**
     * Stream a message creation.
     *
     * Creates a message with streaming enabled and returns a stream manager.
     *
     * @param array<string, mixed> $params Message parameters (same as create())
     * @return StreamResponse Stream manager for consuming events
     */
    public function stream(array $params = []): StreamResponse
    {
        $params['stream'] = true;
        return $this->create($params);
    }

    /**
     * Count tokens in a message.
     *
     * Counts the number of tokens that would be used for a message without
     * actually creating the message.
     *
     * @param array<string, mixed> $params Token counting parameters:
     *   - model: string (required) - Model to use
     *   - messages: array (required) - Messages to count
     *   - system: string|array (optional) - System prompt
     *   - tools: array (optional) - Tool definitions
     *   - tool_choice: string|array (optional) - Tool selection
     *   - thinking: array (optional) - Extended thinking config
     *
     * @return MessageTokensCount Token count response
     */
    public function countTokens(array $params = []): MessageTokensCount
    {
        // Validate required parameters
        if (!isset($params['model']) || !isset($params['messages'])) {
            throw new \InvalidArgumentException('Missing required parameters: model, messages');
        }

        $body = Transform::transform($params, $this->getCountTokensParamTypes());

        $response = $this->_post('/v1/messages/count_tokens', $body);
        return $this->_createMessageTokensCountFromArray($response);
    }

    /**
     * Get parameter type hints for request transformation.
     *
     * @return array<string, mixed>
     */
    private function getParamTypes(): array
    {
        return [
            'model' => ['type' => 'string'],
            'max_tokens' => ['type' => 'int'],
            'messages' => ['type' => 'array'],
            'system' => ['type' => 'string|array'],
            'temperature' => ['type' => 'float'],
            'top_p' => ['type' => 'float'],
            'top_k' => ['type' => 'int'],
            'stop_sequences' => ['type' => 'array'],
            'tools' => ['type' => 'array'],
            'tool_choice' => ['type' => 'string|array'],
            'thinking' => ['type' => 'array'],
            'stream' => ['type' => 'bool'],
            'metadata' => ['type' => 'array'],
            'service_tier' => ['type' => 'string'],
        ];
    }

    /**
     * Get parameter type hints for token counting.
     *
     * @return array<string, mixed>
     */
    private function getCountTokensParamTypes(): array
    {
        return [
            'model' => ['type' => 'string'],
            'messages' => ['type' => 'array'],
            'system' => ['type' => 'string|array'],
            'tools' => ['type' => 'array'],
            'tool_choice' => ['type' => 'string|array'],
            'thinking' => ['type' => 'array'],
        ];
    }

    /**
     * Convert an array response into a Message object.
     *
     * @param array<string, mixed> $data
     * @return Message
     */
    private function _createMessageFromArray(array $data): Message
    {
        return new Message(
            id: $data['id'] ?? '',
            type: $data['type'] ?? 'message',
            role: $data['role'] ?? 'assistant',
            content: $data['content'] ?? [],
            model: $data['model'] ?? '',
            stop_reason: $data['stop_reason'] ?? '',
            stop_sequence: $data['stop_sequence'] ?? null,
            usage: new Usage(
                input_tokens: $data['usage']['input_tokens'] ?? 0,
                output_tokens: $data['usage']['output_tokens'] ?? 0,
                cache_creation_input_tokens: $data['usage']['cache_creation_input_tokens'] ?? null,
                cache_read_input_tokens: $data['usage']['cache_read_input_tokens'] ?? null,
                server_tool_use: $data['usage']['server_tool_use'] ?? null,
            ),
        );
    }

    /**
     * Convert an array response into a MessageTokensCount object.
     *
     * @param array<string, mixed> $data
     * @return MessageTokensCount
     */
    private function _createMessageTokensCountFromArray(array $data): MessageTokensCount
    {
        return new MessageTokensCount(
            input_tokens: $data['input_tokens'] ?? 0,
            output_tokens: $data['output_tokens'] ?? 0,
            cache_creation_input_tokens: $data['cache_creation_input_tokens'] ?? null,
            cache_read_input_tokens: $data['cache_read_input_tokens'] ?? null,
        );
    }
}
