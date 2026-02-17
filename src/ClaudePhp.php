<?php

declare(strict_types=1);

namespace ClaudePhp;

use ClaudePhp\Client\HttpClient as TransportHttpClient;
use ClaudePhp\Resources\Beta\Beta;
use ClaudePhp\Resources\Completions;
use ClaudePhp\Resources\Messages\Messages;
use ClaudePhp\Resources\Models;
use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Main Claude PHP SDK client
 *
 * This is the entry point for the Claude API SDK. It acts as a factory for resource instances
 * and configures the underlying HTTP client.
 *
 * @example
 * ```php
 * $client = new ClaudePhp(apiKey: $_ENV['ANTHROPIC_API_KEY']);
 * $response = $client->messages->create([
 *     'model' => 'claude-sonnet-4-5-20250929',
 *     'max_tokens' => 1024,
 *     'messages' => [
 *         ['role' => 'user', 'content' => 'Hello!']
 *     ]
 * ]);
 * ```
 */
class ClaudePhp
{
    public const DEFAULT_BASE_URL = 'https://api.anthropic.com/v1';
    public const DEFAULT_TIMEOUT = 30.0;
    public const DEFAULT_MAX_RETRIES = 2;
    public const DEFAULT_API_VERSION = '2023-06-01';
    public const SDK_VERSION = '0.6.0';

    private string $apiKey;
    private string $baseUrl;
    private float $timeout;
    private int $maxRetries;

    /**
     * @var array<string, string>
     */
    private array $customHeaders;

    private ?ClientInterface $httpClient;
    private ?RequestFactoryInterface $requestFactory;
    private ?StreamFactoryInterface $streamFactory;
    private ?TransportHttpClient $transport = null;

    /**
     * @var null|Messages
     */
    private $messages;

    /**
     * @var null|Models
     */
    private $models;

    /**
     * @var null|Completions
     */
    private $completions;

    /**
     * @var null|Beta
     */
    private $beta;

    /**
     * Create a new Anthropic client instance
     *
     * @param null|string $apiKey API key (defaults to ANTHROPIC_API_KEY env var)
     * @param string $baseUrl API base URL
     * @param float $timeout Request timeout in seconds
     * @param int $maxRetries Maximum number of retries for retryable errors
     * @param array<string, string> $customHeaders Additional headers for all requests
     * @param null|ClientInterface $httpClient PSR-18 HTTP client (optional)
     * @param null|RequestFactoryInterface $requestFactory PSR-17 request factory
     * @param null|StreamFactoryInterface $streamFactory PSR-17 stream factory
     */
    public function __construct(
        ?string $apiKey = null,
        string $baseUrl = self::DEFAULT_BASE_URL,
        float $timeout = self::DEFAULT_TIMEOUT,
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        array $customHeaders = [],
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->apiKey = $apiKey ?? $_ENV['ANTHROPIC_API_KEY'] ?? '';
        $this->baseUrl = $baseUrl;
        $this->timeout = $timeout;
        $this->maxRetries = $maxRetries;
        $this->customHeaders = $customHeaders;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;

        // Validate that either API key or custom auth headers are provided
        $hasApiKey = '' !== $this->apiKey;
        $hasAuthHeader = isset($customHeaders['x-api-key']) 
            || isset($customHeaders['authorization']) 
            || isset($customHeaders['Authorization']);

        if (!$hasApiKey && !$hasAuthHeader) {
            throw new \InvalidArgumentException(
                'Authentication is required. Provide an API key via the apiKey parameter, '
                . 'ANTHROPIC_API_KEY environment variable, or custom authentication headers '
                . '(x-api-key, Authorization).',
            );
        }

        $this->bootstrapDefaultHttpStack();
    }

    /**
     * Get the API key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get the base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the request timeout
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * Get the maximum number of retries
     */
    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    /**
     * Get custom headers
     *
     * @return array<string, string>
     */
    public function getCustomHeaders(): array
    {
        return $this->customHeaders;
    }

    /**
     * Get the HTTP client
     */
    public function getHttpClient(): ?ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Get the configured HTTP transport wrapper.
     *
     * @throws \RuntimeException if required PSR dependencies were not provided
     */
    public function getHttpTransport(): TransportHttpClient
    {
        if (null !== $this->transport) {
            return $this->transport;
        }

        if (null === $this->httpClient || null === $this->requestFactory || null === $this->streamFactory) {
            throw new \RuntimeException(
                'HTTP client is not configured. Provide a PSR-18 client and PSR-17 factories when constructing '
                    . 'the ClaudePhp client.',
            );
        }

        $this->transport = new TransportHttpClient(
            client: $this->httpClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            defaultHeaders: $this->buildDefaultHeaders(),
            timeout: $this->timeout,
        );

        return $this->transport;
    }

    /**
     * Get the Messages resource
     */
    public function messages(): Messages
    {
        if (null === $this->messages) {
            $this->messages = new Messages($this);
        }

        return $this->messages;
    }

    /**
     * Get the Models resource
     */
    public function models(): Models
    {
        if (null === $this->models) {
            $this->models = new Models($this);
        }

        return $this->models;
    }

    /**
     * Get the Completions resource
     */
    public function completions(): Completions
    {
        if (null === $this->completions) {
            $this->completions = new Completions($this);
        }

        return $this->completions;
    }

    /**
     * Get the Beta resource wrapper
     */
    public function beta(): Beta
    {
        if (null === $this->beta) {
            $this->beta = new Beta($this);
        }

        return $this->beta;
    }

    /**
     * Get an async proxy that exposes async resource operations.
     */
    public function async(): ClaudePhpAsyncProxy
    {
        return new ClaudePhpAsyncProxy($this);
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
            'user-agent' => sprintf('ClaudePhp/%s', self::SDK_VERSION),
        ];

        // Only add x-api-key if an API key was provided
        // This allows alternative auth methods via custom headers
        if ('' !== $this->apiKey) {
            $defaults['x-api-key'] = $this->apiKey;
        }

        return array_merge($defaults, $this->customHeaders);
    }

    /**
     * Ensure a PSR-18 client and PSR-17 factories are always available.
     */
    private function bootstrapDefaultHttpStack(): void
    {
        if (null === $this->requestFactory || null === $this->streamFactory) {
            $factory = new Psr17Factory();
            $this->requestFactory ??= $factory;
            $this->streamFactory ??= $factory;
        }

        if (null === $this->httpClient) {
            $this->httpClient = new GuzzleClient([
                'base_uri' => $this->baseUrl,
                'timeout' => $this->timeout,
            ]);
        }
    }
}
