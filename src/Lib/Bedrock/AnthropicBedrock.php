<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Bedrock;

use ClaudePhp\Lib\Streaming\MessageStreamManager;
use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\Usage;

/**
 * Anthropic client for AWS Bedrock.
 *
 * Wraps the Bedrock Runtime API to provide the Anthropic SDK interface
 * for Claude models hosted on AWS Bedrock.
 */
class AnthropicBedrock
{
    /**
     * @var mixed The AWS Bedrock Runtime client (BedrockRuntimeClient)
     */
    private mixed $bedrock;

    /**
     * @var null|string AWS region
     */
    private ?string $region;

    /**
     * @var null|string API key for Bearer token auth (skips SigV4)
     */
    private ?string $apiKey;

    /**
     * Create a new AnthropicBedrock client.
     *
     * @param array<string, mixed> $config AWS SDK configuration
     * @param null|string $region AWS region
     * @param null|string $apiKey Bearer token (env: AWS_BEARER_TOKEN_BEDROCK); mutually exclusive with explicit AWS keys
     */
    public function __construct(
        array $config = [],
        ?string $region = null,
        ?string $apiKey = null,
    ) {
        $this->apiKey = $apiKey ?? ($_ENV['AWS_BEARER_TOKEN_BEDROCK'] ?? null);
        $this->region = $region;

        if (null !== $this->apiKey && !empty($config['credentials'])) {
            throw new \InvalidArgumentException(
                'Cannot specify both apiKey and explicit AWS credentials. '
                . 'Use either Bearer token auth or SigV4, not both.'
            );
        }

        if (null === $this->apiKey) {
            $bedrockClass = 'Aws\BedrockRuntime\BedrockRuntimeClient';
            if (\class_exists($bedrockClass)) {
                $this->bedrock = new $bedrockClass($config);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    public function authHeaders(): array
    {
        if (null !== $this->apiKey) {
            return ['Authorization' => 'Bearer ' . $this->apiKey];
        }

        return [];
    }

    /**
     * Create a message via Bedrock.
     *
     * @param array<string, mixed> $params Message parameters
     */
    public function createMessage(array $params): Message
    {
        $bedrockParams = $this->transformParams($params);

        $response = $this->bedrock->invokeModel([
            'modelId' => $this->getBedrockModelId($params['model'] ?? ''),
            'body' => \json_encode($bedrockParams),
        ]);

        $body = \json_decode($response['body'], true);

        return $this->transformResponse($body);
    }

    /**
     * Create a message with streaming via Bedrock.
     *
     * @param array<string, mixed> $params Message parameters
     * @param null|callable $onChunk Optional callback for each chunk
     */
    public function createMessageStream(
        array $params,
        ?callable $onChunk = null,
    ): Message {
        $bedrockParams = $this->transformParams($params);
        $bedrockParams['stream'] = true;

        $response = $this->bedrock->invokeModelWithResponseStream([
            'modelId' => $this->getBedrockModelId($params['model'] ?? ''),
            'body' => \json_encode($bedrockParams),
        ]);

        $manager = new MessageStreamManager();
        $eventStream = $response['body'];

        foreach ($this->parseEventStream($eventStream) as $event) {
            if (null !== $onChunk) {
                ($onChunk)($event);
            }
            $manager->addEvent($event);
        }

        return $manager->getMessage();
    }

    /**
     * Transform SDK params to Bedrock format.
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
     * Convert model ID to Bedrock model ID.
     *
     * @param string $modelId SDK model ID
     *
     * @return string Bedrock model ID
     */
    /**
     * Convert short model aliases to AWS Bedrock model IDs.
     *
     * Pass full Bedrock model IDs directly (e.g. "anthropic.claude-opus-4-7-20260416-v1:0")
     * to bypass mapping. Unknown IDs are passed through unchanged.
     */
    private function getBedrockModelId(string $modelId): string
    {
        // Already a Bedrock-formatted model ID — pass through.
        if (str_starts_with($modelId, 'anthropic.') || str_starts_with($modelId, 'arn:')) {
            return $modelId;
        }

        return match ($modelId) {
            'claude-opus-4-7' => 'anthropic.claude-opus-4-7-20260416-v1:0',
            'claude-opus-4-6' => 'anthropic.claude-opus-4-6-20260205-v1:0',
            'claude-sonnet-4-6' => 'anthropic.claude-sonnet-4-6-20260217-v1:0',
            'claude-opus-4-5', 'claude-opus-4-5-20251101' => 'anthropic.claude-opus-4-5-20251101-v1:0',
            'claude-sonnet-4-5', 'claude-sonnet-4-5-20250929' => 'anthropic.claude-sonnet-4-5-20250929-v1:0',
            'claude-haiku-4-5', 'claude-haiku-4-5-20251001' => 'anthropic.claude-haiku-4-5-20251001-v1:0',
            'claude-opus-4-1-20250805' => 'anthropic.claude-opus-4-1-20250805-v1:0',
            'claude-opus-4-20250514', 'claude-opus-4-0' => 'anthropic.claude-opus-4-20250514-v1:0',
            'claude-sonnet-4-20250514', 'claude-sonnet-4-0' => 'anthropic.claude-sonnet-4-20250514-v1:0',
            'claude-3-7-sonnet-20250219', 'claude-3-7-sonnet-latest' => 'anthropic.claude-3-7-sonnet-20250219-v1:0',
            'claude-3-5-haiku-20241022', 'claude-3-5-haiku-latest' => 'anthropic.claude-3-5-haiku-20241022-v1:0',
            'claude-3-opus-20240229', 'claude-3-opus-latest' => 'anthropic.claude-3-opus-20240229-v1:0',
            'claude-3-haiku-20240307' => 'anthropic.claude-3-haiku-20240307-v1:0',
            default => $modelId,
        };
    }

    /**
     * Transform Bedrock response to SDK format.
     *
     * @param array<string, mixed> $bedrockResponse
     */
    private function transformResponse(array $bedrockResponse): Message
    {
        $content = [];
        foreach ($bedrockResponse['content'] ?? [] as $block) {
            $content[] = [
                'type' => $block['type'] ?? 'text',
                'text' => $block['text'] ?? '',
            ];
        }

        $usage = new Usage(
            input_tokens: $bedrockResponse['usage']['input_tokens'] ?? 0,
            output_tokens: $bedrockResponse['usage']['output_tokens'] ?? 0,
            cache_creation_input_tokens: $bedrockResponse['usage']['cache_creation_input_tokens'] ?? null,
            cache_read_input_tokens: $bedrockResponse['usage']['cache_read_input_tokens'] ?? null,
            server_tool_use: $bedrockResponse['usage']['server_tool_use'] ?? null,
        );

        return new Message(
            id: $bedrockResponse['id'] ?? \uniqid('bedrock-'),
            type: 'message',
            role: 'assistant',
            content: $content,
            model: $bedrockResponse['model'] ?? 'unknown',
            stop_reason: $bedrockResponse['stop_reason'] ?? 'end_turn',
            usage: $usage,
        );
    }

    /**
     * Parse AWS EventStream format.
     *
     * @param mixed $eventStream Event stream from Bedrock
     *
     * @return \Generator<array<string, mixed>>
     */
    private function parseEventStream(mixed $eventStream): \Generator
    {
        // This is a simplified parser - in production, use AWS SDK utilities
        if (\is_string($eventStream)) {
            foreach (\explode("\n", $eventStream) as $line) {
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
        }
    }
}
