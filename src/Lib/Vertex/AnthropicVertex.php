<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Vertex;

use ClaudePhp\Lib\Streaming\MessageStreamManager;
use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\Usage;

/**
 * Anthropic client for Google Vertex AI.
 *
 * Wraps the Vertex AI API to provide the Anthropic SDK interface
 * for Claude models hosted on Google Cloud Vertex AI.
 */
class AnthropicVertex
{
    /**
     * @var string Google Cloud project ID
     */
    private string $projectId;

    /**
     * @var null|string Google Cloud region
     */
    private ?string $region;

    /**
     * @var null|string API access token
     */
    private ?string $accessToken;

    /**
     * Create a new AnthropicVertex client.
     *
     * @param string $projectId Google Cloud project ID
     * @param null|string $region Google Cloud region (default: us-central1)
     * @param null|string $accessToken Optional access token
     */
    public function __construct(
        string $projectId,
        ?string $region = null,
        ?string $accessToken = null,
    ) {
        $this->projectId = $projectId;
        $this->region = $region ?? 'us-central1';
        $this->accessToken = $accessToken ?? $this->loadAccessToken();
    }

    /**
     * Create a message via Vertex AI.
     *
     * @param array<string, mixed> $params Message parameters
     */
    public function createMessage(array $params): Message
    {
        $vertexParams = $this->transformParams($params);
        $modelId = $this->getVertexModelId($params['model'] ?? 'claude-sonnet-4-5');

        $url = "https://{$this->region}-aiplatform.googleapis.com/v1beta1/projects/{$this->projectId}/locations/{$this->region}/publishers/anthropic/models/{$modelId}:rawPredict";

        $response = $this->makeRequest('POST', $url, $vertexParams);

        return $this->transformResponse($response, $params['model'] ?? 'unknown');
    }

    /**
     * Create a message with streaming via Vertex AI.
     *
     * @param array<string, mixed> $params Message parameters
     * @param null|callable $onChunk Optional callback for each chunk
     */
    public function createMessageStream(
        array $params,
        ?callable $onChunk = null,
    ): Message {
        $vertexParams = $this->transformParams($params);
        $vertexParams['stream'] = true;
        $modelId = $this->getVertexModelId($params['model'] ?? 'claude-sonnet-4-5');

        $url = "https://{$this->region}-aiplatform.googleapis.com/v1beta1/projects/{$this->projectId}/locations/{$this->region}/publishers/anthropic/models/{$modelId}:streamRawPredict";

        $manager = new MessageStreamManager();

        $stream = $this->makeStreamRequest('POST', $url, $vertexParams);
        foreach ($stream as $event) {
            if (null !== $onChunk) {
                ($onChunk)($event);
            }
            $manager->addEvent($event);
        }

        return $manager->getMessage();
    }

    /**
     * Transform SDK params to Vertex AI format.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function transformParams(array $params): array
    {
        return [
            'anthropic_version' => '2023-06-01',
            'max_tokens' => $params['max_tokens'] ?? 1024,
            'messages' => $params['messages'] ?? [],
            'system' => $params['system'] ?? null,
            'tools' => $params['tools'] ?? null,
            'temperature' => $params['temperature'] ?? null,
            'top_p' => $params['top_p'] ?? null,
        ];
    }

    /**
     * Convert model ID to Vertex AI model ID.
     *
     * @param string $modelId SDK model ID
     *
     * @return string Vertex AI model ID
     */
    private function getVertexModelId(string $modelId): string
    {
        return match ($modelId) {
            'claude-sonnet-4-5', 'claude-sonnet-4-5-20250929' => 'claude-3-5-sonnet@20241022',
            'claude-haiku-4-5', 'claude-haiku-4-5-20251001' => 'claude-3-5-haiku@20241022',
            'claude-opus-4-1', 'claude-opus-4-1-20250805' => 'claude-3-opus@20240229',
            default => $modelId,
        };
    }

    /**
     * Transform Vertex AI response to SDK format.
     *
     * @param array<string, mixed> $vertexResponse
     */
    private function transformResponse(array $vertexResponse, string $model): Message
    {
        $content = [];
        foreach ($vertexResponse['content'] ?? [] as $block) {
            $content[] = [
                'type' => $block['type'] ?? 'text',
                'text' => $block['text'] ?? '',
            ];
        }

        $usage = new Usage(
            input_tokens: $vertexResponse['usage']['input_tokens'] ?? 0,
            output_tokens: $vertexResponse['usage']['output_tokens'] ?? 0,
            cache_creation_input_tokens: $vertexResponse['usage']['cache_creation_input_tokens'] ?? null,
            cache_read_input_tokens: $vertexResponse['usage']['cache_read_input_tokens'] ?? null,
            server_tool_use: $vertexResponse['usage']['server_tool_use'] ?? null,
        );

        return new Message(
            id: $vertexResponse['id'] ?? \uniqid('vertex-'),
            type: 'message',
            role: 'assistant',
            content: $content,
            model: $model,
            stop_reason: $vertexResponse['stop_reason'] ?? 'end_turn',
            usage: $usage,
        );
    }

    /**
     * Load access token from Google Cloud auth.
     */
    private function loadAccessToken(): ?string
    {
        // In production, use google/auth library
        // For now, return null - caller should provide token
        return null;
    }

    /**
     * Make an HTTP request to Vertex AI API.
     *
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param array<string, mixed> $data Request body
     *
     * @return array<string, mixed>
     */
    private function makeRequest(string $method, string $url, array $data): array
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . ($this->accessToken ?? ''),
        ];

        $context = \stream_context_create([
            'http' => [
                'method' => $method,
                'header' => \implode("\r\n", $headers),
                'content' => \json_encode($data),
            ],
        ]);

        $response = @\file_get_contents($url, false, $context);
        if (false === $response) {
            throw new \RuntimeException('Vertex AI API request failed');
        }

        return \json_decode($response, true) ?? [];
    }

    /**
     * Make a streaming request to Vertex AI API.
     *
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param array<string, mixed> $data Request body
     *
     * @return \Generator<array<string, mixed>>
     */
    private function makeStreamRequest(
        string $method,
        string $url,
        array $data,
    ): \Generator {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . ($this->accessToken ?? ''),
        ];

        $context = \stream_context_create([
            'http' => [
                'method' => $method,
                'header' => \implode("\r\n", $headers),
                'content' => \json_encode($data),
            ],
        ]);

        $stream = @\fopen($url, 'r', false, $context);
        if (false === $stream) {
            throw new \RuntimeException('Vertex AI stream request failed');
        }

        while (!feof($stream)) {
            $line = \fgets($stream);
            if (false === $line) {
                break;
            }

            $line = \trim($line);
            if (empty($line)) {
                continue;
            }

            try {
                $event = \json_decode($line, true);
                if (\is_array($event)) {
                    yield $event;
                }
            } catch (\JsonException) {
                continue;
            }
        }

        \fclose($stream);
    }
}
