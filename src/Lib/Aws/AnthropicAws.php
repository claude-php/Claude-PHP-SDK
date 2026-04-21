<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Aws;

/**
 * Anthropic client for AWS (shared auth logic for Bedrock/Mantle).
 *
 * Supports both SigV4 and API key (Bearer token) authentication.
 *
 * Mirrors Python `src/anthropic/lib/aws/_client.py`.
 */
class AnthropicAws
{
    private ?string $apiKey;
    private ?Credentials $credentials = null;
    private ?SigV4 $signer = null;
    private string $region;
    private string $service;

    public function __construct(
        ?string $apiKey = null,
        ?string $awsAccessKey = null,
        ?string $awsSecretKey = null,
        ?string $awsSessionToken = null,
        ?string $awsRegion = null,
        ?string $awsProfile = null,
        string $service = 'bedrock',
    ) {
        $this->apiKey = $apiKey
            ?? ($_ENV['AWS_BEARER_TOKEN_BEDROCK'] ?? null)
            ?? ($_ENV['ANTHROPIC_AWS_API_KEY'] ?? null);
        $this->region = $awsRegion ?? ($_ENV['AWS_REGION'] ?? 'us-east-1');
        $this->service = $service;

        if (null !== $this->apiKey && (null !== $awsAccessKey || null !== $awsProfile)) {
            throw new \InvalidArgumentException(
                'Cannot specify both apiKey and explicit AWS credentials/profile. '
                . 'Use either Bearer token auth or SigV4, not both.'
            );
        }

        if (null === $this->apiKey) {
            try {
                $this->credentials = Credentials::resolve(
                    accessKey: $awsAccessKey,
                    secretKey: $awsSecretKey,
                    sessionToken: $awsSessionToken,
                    profile: $awsProfile,
                );
                $this->signer = new SigV4($this->credentials, $this->region, $this->service);
            } catch (\RuntimeException) {
                // Defer credential errors until first request — allows construction without creds for testing
            }
        }
    }

    /**
     * Get authentication headers for a request.
     *
     * For Bearer auth, returns the Authorization header directly.
     * For SigV4, returns SigV4-signed headers (requires method/url/body).
     *
     * @param array<string, string> $extraHeaders Additional headers to include in the canonical request
     * @return array<string, string>
     */
    public function authHeaders(
        string $method = 'POST',
        string $url = '',
        array $extraHeaders = [],
        string $body = '',
    ): array {
        if (null !== $this->apiKey) {
            return ['Authorization' => 'Bearer ' . $this->apiKey];
        }

        if (null !== $this->signer && '' !== $url) {
            return $this->signer->signRequest($method, $url, $extraHeaders, $body);
        }

        return [];
    }

    public function useBearerAuth(): bool
    {
        return null !== $this->apiKey;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getCredentials(): ?Credentials
    {
        return $this->credentials;
    }

    public function getSigner(): ?SigV4
    {
        return $this->signer;
    }
}
