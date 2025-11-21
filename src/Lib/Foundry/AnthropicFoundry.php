<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Foundry;

use ClaudePhp\Client\HttpClient;
use ClaudePhp\Lib\Streaming\MessageStreamManager;
use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\Usage;
use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Anthropic client for Microsoft Azure AI Foundry.
 *
 * Provides access to Claude models through Microsoft's Azure AI Foundry platform.
 * Supports both API key and Azure AD token authentication.
 *
 * @example
 * ```php
 * // Using API key authentication
 * $client = new AnthropicFoundry(
 *     resource: 'my-resource',
 *     apiKey: 'your-foundry-api-key'
 * );
 *
 * // Using Azure AD token authentication
 * $client = new AnthropicFoundry(
 *     resource: 'my-resource',
 *     azureAdTokenProvider: fn() => getAzureAdToken()
 * );
 * ```
 *
 * @see https://aka.ms/foundry/claude/docs
 * @see https://docs.claude.com/en/docs/build-with-claude/claude-in-microsoft-foundry
 */
class AnthropicFoundry
{
    private const DEFAULT_TIMEOUT = 30.0;
    private const DEFAULT_API_VERSION = '2023-06-01';
    private const SDK_VERSION = '0.1.0';
    private const AZURE_AD_SCOPE = 'https://ai.azure.com/.default';

    private string $resource;
    private ?string $apiKey;
    private mixed $azureAdTokenProvider;
    private float $timeout;
    private HttpClient $httpClient;

    /**
     * @var array<string, string>
     */
    private array $customHeaders;

    /**
     * Create a new AnthropicFoundry client.
     *
     * You must provide either an API key or an Azure AD token provider for authentication.
     *
     * @param string $resource Azure Foundry resource name
     * @param null|string $apiKey Foundry API key (for API key authentication)
     * @param null|callable $azureAdTokenProvider Callable that returns Azure AD token (for Azure AD authentication)
     * @param float $timeout Request timeout in seconds
     * @param array<string, string> $customHeaders Additional headers for all requests
     * @param null|ClientInterface $httpClient PSR-18 HTTP client (optional)
     * @param null|RequestFactoryInterface $requestFactory PSR-17 request factory
     * @param null|StreamFactoryInterface $streamFactory PSR-17 stream factory
     */
    public function __construct(
        string $resource,
        ?string $apiKey = null,
        ?callable $azureAdTokenProvider = null,
        float $timeout = self::DEFAULT_TIMEOUT,
        array $customHeaders = [],
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
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

        // Bootstrap default HTTP stack
        $factory = $requestFactory ?? new Psr17Factory();
        $requestFactory = $factory;
        $streamFactory ??= $factory;

        $baseUrl = $this->getBaseUrl();

        $httpClient ??= new GuzzleClient([
            'base_uri' => $baseUrl,
            'timeout' => $timeout,
        ]);

        $this->httpClient = new HttpClient(
            client: $httpClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
            defaultHeaders: $this->buildDefaultHeaders(),
            timeout: $timeout,
        );
    }

    /**
     * Create a message via Azure Foundry.
     *
     * @param array<string, mixed> $params Message parameters
     */
    public function createMessage(array $params): Message
    {
        $url = $this->getBaseUrl() . '/v1/messages';
        $headers = $this->getAuthHeaders();

        $response = $this->httpClient->post($url, $params, $headers);

        return $this->transformResponse($response);
    }

    /**
     * Create a message with streaming via Azure Foundry.
     *
     * @param array<string, mixed> $params Message parameters
     *
     * @return \Generator<array<string, mixed>>
     */
    public function createMessageStream(array $params): \Generator
    {
        $params['stream'] = true;
        $url = $this->getBaseUrl() . '/v1/messages';
        $headers = $this->getAuthHeaders();

        $response = $this->httpClient->postStream($url, $params, $headers);
        $body = $response->getBody();

        $buffer = '';
        while (!$body->eof()) {
            $chunk = $body->read(8192);
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
                    break 2;
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
     * Create a message with streaming and accumulate into a Message object.
     *
     * @param array<string, mixed> $params Message parameters
     * @param null|callable $onChunk Optional callback for each chunk
     */
    public function createMessageStreamAccumulated(
        array $params,
        ?callable $onChunk = null,
    ): Message {
        $manager = new MessageStreamManager();

        foreach ($this->createMessageStream($params) as $event) {
            if (null !== $onChunk) {
                ($onChunk)($event);
            }
            $manager->addEvent($event);
        }

        return $manager->getMessage();
    }

    /**
     * Count tokens for a message request.
     *
     * @param array<string, mixed> $params Message parameters
     *
     * @return object{input_tokens: int}
     */
    public function countTokens(array $params): object
    {
        $url = $this->getBaseUrl() . '/v1/messages/count_tokens';
        $headers = $this->getAuthHeaders();

        $response = $this->httpClient->post($url, $params, $headers);

        return (object) $response;
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
            'user-agent' => sprintf('ClaudePhp/%s (Foundry)', self::SDK_VERSION),
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
