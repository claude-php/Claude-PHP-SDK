<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Bedrock;

use ClaudePhp\Client\HttpClient;
use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;

/**
 * Anthropic client for AWS Bedrock Mantle.
 *
 * Mantle provides a direct HTTP API to Claude on Bedrock with Bearer token auth.
 * Default base URL: https://bedrock-mantle.{region}.api.aws/anthropic
 *
 * Mirrors Python `src/anthropic/lib/bedrock/_mantle.py`.
 */
class AnthropicBedrockMantle
{
    public const ENV_BASE_URL = 'ANTHROPIC_BEDROCK_MANTLE_BASE_URL';
    public const API_KEY_ENV_VARS = ['AWS_BEARER_TOKEN_BEDROCK', 'ANTHROPIC_AWS_API_KEY'];

    private ?string $apiKey;
    private string $region;
    private string $baseUrl;
    private bool $skipAuth;
    private HttpClient $http;

    public function __construct(
        ?string $apiKey = null,
        ?string $region = null,
        ?string $baseUrl = null,
        bool $skipAuth = false,
        ?string $awsAccessKey = null,
        ?string $awsSecretKey = null,
        ?string $awsSessionToken = null,
        ?string $awsProfile = null,
        ?HttpClient $http = null,
        float $timeout = 30.0,
    ) {
        $this->region = $region ?? ($_ENV['AWS_REGION'] ?? 'us-east-1');
        $this->skipAuth = $skipAuth;

        $this->apiKey = $apiKey;
        if (null === $this->apiKey) {
            foreach (self::API_KEY_ENV_VARS as $envVar) {
                if (isset($_ENV[$envVar]) && '' !== $_ENV[$envVar]) {
                    $this->apiKey = $_ENV[$envVar];
                    break;
                }
            }
        }

        if (null !== $this->apiKey && (null !== $awsAccessKey || null !== $awsProfile)) {
            throw new \InvalidArgumentException(
                'Cannot specify both apiKey and explicit AWS credentials/profile for Mantle client.'
            );
        }

        $this->baseUrl = rtrim(
            $baseUrl
                ?? ($_ENV[self::ENV_BASE_URL] ?? null)
                ?? "https://bedrock-mantle.{$this->region}.api.aws/anthropic",
            '/',
        );

        if (null === $http) {
            $factory = new Psr17Factory();
            $http = new HttpClient(
                client: new GuzzleClient(['timeout' => $timeout]),
                requestFactory: $factory,
                streamFactory: $factory,
                defaultHeaders: $this->buildDefaultHeaders(),
                timeout: $timeout,
            );
        }
        $this->http = $http;
    }

    /**
     * @return array<string, string>
     */
    public function authHeaders(): array
    {
        if ($this->skipAuth) {
            return [];
        }

        if (null !== $this->apiKey) {
            return ['Authorization' => 'Bearer ' . $this->apiKey];
        }

        return [];
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getHttpClient(): HttpClient
    {
        return $this->http;
    }

    /**
     * Get the Mantle Beta sub-resource (messages-only).
     */
    public function beta(): MantleBeta
    {
        return new MantleBeta($this);
    }

    /**
     * @return array<string, string>
     */
    private function buildDefaultHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'claude-php-sdk-mantle/0.7.0',
        ];

        return array_merge($headers, $this->authHeaders());
    }
}

/**
 * Mantle Beta resource - only exposes messages().
 */
class MantleBeta
{
    public function __construct(
        private readonly AnthropicBedrockMantle $mantle,
    ) {
    }

    public function messages(): MantleBetaMessages
    {
        return new MantleBetaMessages($this->mantle);
    }
}

/**
 * Mantle Beta Messages resource — POSTs through the SDK's HttpClient.
 */
class MantleBetaMessages
{
    public function __construct(
        private readonly AnthropicBedrockMantle $mantle,
    ) {
    }

    /**
     * Create a message via Bedrock Mantle.
     *
     * @param array<string, mixed> $params
     * @return mixed Decoded JSON response
     */
    public function create(array $params = []): mixed
    {
        $url = $this->mantle->getBaseUrl() . '/v1/messages';

        return $this->mantle->getHttpClient()->post($url, $params, $this->mantle->authHeaders());
    }
}
