<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Foundry;

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use ClaudePhp\Lib\Streaming\AsyncMessageStreamManager;
use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\Usage;

/**
 * Async Anthropic client for Microsoft Azure AI Foundry.
 *
 * Provides asynchronous access to Claude models through Microsoft's Azure AI Foundry platform
 * using Amphp for concurrent operations.
 *
 * @example
 * ```php
 * use Amp\Loop;
 * use ClaudePhp\Lib\Foundry\AsyncAnthropicFoundry;
 *
 * Loop::run(function () {
 *     $client = new AsyncAnthropicFoundry(
 *         resource: 'my-resource',
 *         apiKey: 'your-foundry-api-key'
 *     );
 *
 *     $message = yield $client->createMessage([
 *         'model' => 'claude-sonnet-4-5',
 *         'max_tokens' => 1024,
 *         'messages' => [['role' => 'user', 'content' => 'Hello!']]
 *     ]);
 *
 *     echo $message->content[0]->text;
 * });
 * ```
 *
 * @see https://aka.ms/foundry/claude/docs
 * @see https://docs.claude.com/en/docs/build-with-claude/claude-in-microsoft-foundry
 */
class AsyncAnthropicFoundry
{
    private const DEFAULT_TIMEOUT = 30.0;
    private const DEFAULT_API_VERSION = '2023-06-01';
    private const SDK_VERSION = '0.1.0';
    private const AZURE_AD_SCOPE = 'https://ai.azure.com/.default';

    private string $resource;
    private ?string $apiKey;
    private mixed $azureAdTokenProvider;
    private float $timeout;

    /**
     * @var array<string, string>
     */
    private array $customHeaders;

    private mixed $httpClient;

    /**
     * Create a new AsyncAnthropicFoundry client.
     *
     * You must provide either an API key or an Azure AD token provider for authentication.
     *
     * @param string $resource Azure Foundry resource name
     * @param null|string $apiKey Foundry API key (for API key authentication)
     * @param null|callable $azureAdTokenProvider Callable that returns Azure AD token (for Azure AD authentication)
     * @param float $timeout Request timeout in seconds
     * @param array<string, string> $customHeaders Additional headers for all requests
     */
    public function __construct(
        string $resource,
        ?string $apiKey = null,
        ?callable $azureAdTokenProvider = null,
        float $timeout = self::DEFAULT_TIMEOUT,
        array $customHeaders = [],
    ) {
        if (null === $apiKey && null === $azureAdTokenProvider) {
            throw new \InvalidArgumentException(
                'Either apiKey or azureAdTokenProvider must be provided for authentication.',
            );
        }

        $this->resource = $resource;
        $this->apiKey = $apiKey;
        $this->azureAdTokenProvider = $azureAdTokenProvider;
        $this->timeout = $timeout;
        $this->customHeaders = $customHeaders;

        // Build Amp HTTP client
        $this->httpClient = HttpClientBuilder::buildDefault();
    }

    /**
     * Create a message via Azure Foundry (async).
     *
     * @param array<string, mixed> $params Message parameters
     *
     * @return \Generator<Message>
     */
    public function createMessage(array $params): \Generator
    {
        $url = $this->getBaseUrl() . '/v1/messages';
        $headers = array_merge(
            $this->buildDefaultHeaders(),
            $this->getAuthHeaders(),
        );

        $request = new Request($url, 'POST');
        $request->setHeaders($headers);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($params));

        /** @var Response $response */
        $response = yield $this->httpClient->request($request);

        if ($response->getStatus() >= 400) {
            throw new \RuntimeException(
                sprintf('Foundry API error: %d %s', $response->getStatus(), $response->getReason()),
            );
        }

        $body = yield $response->getBody()->buffer();
        $data = json_decode($body, true);

        return $this->transformResponse($data);
    }

    /**
     * Create a message with streaming via Azure Foundry (async).
     *
     * @param array<string, mixed> $params Message parameters
     *
     * @return \Generator<array<string, mixed>>
     */
    public function createMessageStream(array $params): \Generator
    {
        $params['stream'] = true;
        $url = $this->getBaseUrl() . '/v1/messages';
        $headers = array_merge(
            $this->buildDefaultHeaders(),
            $this->getAuthHeaders(),
        );

        $request = new Request($url, 'POST');
        $request->setHeaders($headers);
        $request->setHeader('Content-Type', 'application/json');
        $request->setHeader('Accept', 'text/event-stream');
        $request->setBody(json_encode($params));

        /** @var Response $response */
        $response = yield $this->httpClient->request($request);

        if ($response->getStatus() >= 400) {
            throw new \RuntimeException(
                sprintf('Foundry API error: %d %s', $response->getStatus(), $response->getReason()),
            );
        }

        $body = $response->getBody();
        $buffer = '';

        while (null !== $chunk = yield $body->read()) {
            $buffer .= $chunk;

            // Process complete lines
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                $line = trim($line);
                if (empty($line) || !str_starts_with($line, 'data: ')) {
                    continue;
                }

                $data = substr($line, 6); // Remove "data: " prefix
                if ('[DONE]' === $data) {
                    return;
                }

                try {
                    $event = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($event)) {
                        yield $event;
                    }
                } catch (\JsonException) {
                    continue;
                }
            }
        }
    }

    /**
     * Create a message with streaming and accumulate into a Message object (async).
     *
     * @param array<string, mixed> $params Message parameters
     * @param null|callable $onChunk Optional callback for each chunk
     *
     * @return \Generator<Message>
     */
    public function createMessageStreamAccumulated(
        array $params,
        ?callable $onChunk = null,
    ): \Generator {
        $manager = new AsyncMessageStreamManager();

        $stream = yield $this->createMessageStream($params);
        while (yield $stream->advance()) {
            $event = $stream->getCurrent();
            if (null !== $onChunk) {
                ($onChunk)($event);
            }
            $manager->addEvent($event);
        }

        return $manager->getMessage();
    }

    /**
     * Count tokens for a message request (async).
     *
     * @param array<string, mixed> $params Message parameters
     *
     * @return \Generator<object{input_tokens: int}>
     */
    public function countTokens(array $params): \Generator
    {
        $url = $this->getBaseUrl() . '/v1/messages/count_tokens';
        $headers = array_merge(
            $this->buildDefaultHeaders(),
            $this->getAuthHeaders(),
        );

        $request = new Request($url, 'POST');
        $request->setHeaders($headers);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($params));

        /** @var Response $response */
        $response = yield $this->httpClient->request($request);

        if ($response->getStatus() >= 400) {
            throw new \RuntimeException(
                sprintf('Foundry API error: %d %s', $response->getStatus(), $response->getReason()),
            );
        }

        $body = yield $response->getBody()->buffer();
        $data = json_decode($body, true);

        return (object) $data;
    }

    /**
     * Get the resource name.
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Get the timeout value.
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * Get the base URL for the Foundry API.
     */
    private function getBaseUrl(): string
    {
        return "https://{$this->resource}.api.foundry.azure.ai";
    }

    /**
     * Build default headers for outgoing requests.
     *
     * @return array<string, string>
     */
    private function buildDefaultHeaders(): array
    {
        $defaults = [
            'anthropic-version' => self::DEFAULT_API_VERSION,
            'user-agent' => sprintf('ClaudePhp/%s (Foundry/Async)', self::SDK_VERSION),
        ];

        return array_merge($defaults, $this->customHeaders);
    }

    /**
     * Get authentication headers based on the configured auth method.
     *
     * @return array<string, string>
     */
    private function getAuthHeaders(): array
    {
        if (null !== $this->azureAdTokenProvider) {
            // Azure AD token authentication
            $token = ($this->azureAdTokenProvider)();

            return ['Authorization' => 'Bearer ' . $token];
        }

        // API key authentication
        return ['x-api-key' => $this->apiKey];
    }

    /**
     * Transform Foundry response to SDK Message format.
     *
     * @param array<string, mixed>|object $foundryResponse
     */
    private function transformResponse(array|object $foundryResponse): Message
    {
        $response = is_object($foundryResponse) ? (array) $foundryResponse : $foundryResponse;

        $content = [];
        foreach ($response['content'] ?? [] as $block) {
            $blockArray = is_object($block) ? (array) $block : $block;
            $content[] = (object) $blockArray;
        }

        $usageData = $response['usage'] ?? [];
        if (is_object($usageData)) {
            $usageData = (array) $usageData;
        }

        $usage = new Usage(
            input_tokens: $usageData['input_tokens'] ?? 0,
            output_tokens: $usageData['output_tokens'] ?? 0,
            cache_creation_input_tokens: $usageData['cache_creation_input_tokens'] ?? null,
            cache_read_input_tokens: $usageData['cache_read_input_tokens'] ?? null,
            server_tool_use: $usageData['server_tool_use'] ?? null,
        );

        return new Message(
            id: $response['id'] ?? uniqid('foundry-'),
            type: $response['type'] ?? 'message',
            role: $response['role'] ?? 'assistant',
            content: $content,
            model: $response['model'] ?? 'unknown',
            stop_reason: $response['stop_reason'] ?? null,
            stop_sequence: $response['stop_sequence'] ?? null,
            usage: $usage,
        );
    }
}
