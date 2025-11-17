<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Lib\Parse\ResponseParser;
use ClaudePhp\Lib\Parse\SchemaTransformer;
use ClaudePhp\Lib\Streaming\StructuredOutputStream;
use ClaudePhp\Lib\Tools\BetaToolRunner;
use ClaudePhp\Resources\Resource;
use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\StreamResponse;
use ClaudePhp\Responses\Usage;
use ClaudePhp\Utils\FileExtraction;
use ClaudePhp\Utils\Transform;

/**
 * Messages resource for beta API.
 *
 * Beta variant of the Messages API with additional experimental features.
 */
class Messages extends Resource
{
    /**
     * Create a message using the beta API.
     *
     * @param array<string, mixed> $params Message parameters
     * @return Message|StreamResponse
     */
    public function create(array $params = []): Message|StreamResponse
    {
        $required = ['model', 'max_tokens', 'messages'];
        foreach ($required as $key) {
            if (!isset($params[$key])) {
                throw new \InvalidArgumentException("Missing required parameter: {$key}");
            }
        }

        $body = Transform::transform($params, $this->getParamTypes());
        FileExtraction::extractFiles($body, [['files', '<array>']]);

        if (!empty($params['stream'])) {
            return $this->_postStream('/v1/messages', $body);
        }

        $response = $this->_post('/v1/messages', $body);
        if (!\is_array($response)) {
            throw new \RuntimeException('Unexpected response payload from beta messages API');
        }

        return $this->createMessageFromArray($response);
    }

    /**
     * Stream beta messages with SSE support.
     *
     * @param array<string, mixed> $params
     */
    public function stream(array $params = []): StreamResponse
    {
        $params['stream'] = true;
        $response = $this->create($params);
        if (!$response instanceof StreamResponse) {
            throw new \RuntimeException('Expected streaming response from beta messages stream()');
        }

        return $response;
    }

    /**
     * Parse a structured output response into PHP arrays.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function parse(array $params = []): array
    {
        if (!isset($params['output_format'])) {
            throw new \InvalidArgumentException('output_format is required when calling parse()');
        }

        $schema = $this->normalizeOutputFormat($params['output_format']);
        $params['output_format'] = $schema;
        $params['betas'] = $this->ensureStructuredOutputsBeta($params['betas'] ?? []);

        $result = $this->create($params);
        if (!$result instanceof Message) {
            throw new \RuntimeException('parse() cannot be used with streaming requests');
        }

        return ResponseParser::parse($result, $schema);
    }

    /**
     * Stream structured outputs and receive parsed snapshots in real time.
     *
     * @param array<string, mixed> $params
     */
    public function streamStructured(array $params = []): StructuredOutputStream
    {
        if (!isset($params['output_format'])) {
            throw new \InvalidArgumentException('output_format is required when streaming structured outputs');
        }

        $schema = $this->normalizeOutputFormat($params['output_format']);
        $params['output_format'] = $schema;
        $params['betas'] = $this->ensureStructuredOutputsBeta($params['betas'] ?? []);
        $params['stream'] = true;

        $response = $this->create($params);
        if (!$response instanceof StreamResponse) {
            throw new \RuntimeException('streamStructured() expected a streaming response');
        }

        return new StructuredOutputStream($response, $schema);
    }

    /**
     * Automatically run beta tool loops until completion.
     *
     * @param array<string, mixed> $params Message parameters
     * @return BetaToolRunner
     */
    public function toolRunner(array $params = []): BetaToolRunner
    {
        $tools = $params['tools'] ?? [];
        unset($params['tools']);

        return new BetaToolRunner($this->client, $params, $tools);
    }

    /**
     * Get batches sub-resource.
     *
     * @return Batches
     */
    public function batches(): Batches
    {
        return new Batches($this->client);
    }

    /**
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
            'metadata' => ['type' => 'array'],
            'betas' => ['type' => 'array'],
            'output_format' => ['type' => 'array'],
            'context_management' => ['type' => 'array'],
            'thinking' => ['type' => 'array'],
            'stream' => ['type' => 'bool'],
        ];
    }

    /**
     * Normalize output_format input to JSON schema.
     *
     * @param class-string|array<string, mixed> $format
     * @return array<string, mixed>
     */
    private function normalizeOutputFormat(array|string $format): array
    {
        if (\is_string($format)) {
            if (!class_exists($format)) {
                throw new \InvalidArgumentException("output_format class {$format} does not exist");
            }
            return SchemaTransformer::fromClass($format);
        }

        if (!isset($format['type'])) {
            throw new \InvalidArgumentException('output_format schemas must include a type key');
        }

        return $format;
    }

    /**
     * Ensure the structured outputs beta flag is present.
     *
     * @param array<int, string> $betas
     * @return array<int, string>
     */
    private function ensureStructuredOutputsBeta(array $betas): array
    {
        if (!\in_array('structured-outputs-2025-09-17', $betas, true)) {
            $betas[] = 'structured-outputs-2025-09-17';
        }

        return $betas;
    }

    /**
     * Convert response payload to Message value object.
     *
     * @param array<string, mixed> $data
     */
    private function createMessageFromArray(array $data): Message
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
}
