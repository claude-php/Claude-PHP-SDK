<?php

declare(strict_types=1);

namespace ClaudePhp\Resources;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Responses\StreamResponse;

/**
 * Base resource class for all API resources.
 *
 * Provides shared functionality and access to the main client configuration
 * for all resource classes.
 */
abstract class Resource
{
    /**
     * The main Claude PHP client instance
     */
    protected ClaudePhp $client;

    /**
     * Create a new resource instance
     */
    public function __construct(ClaudePhp $client)
    {
        $this->client = $client;
    }

    /**
     * Get the base URL from the client
     */
    protected function getBaseUrl(): string
    {
        return $this->client->getBaseUrl();
    }

    /**
     * Get the request timeout from the client
     */
    protected function getTimeout(): float
    {
        return $this->client->getTimeout();
    }

    /**
     * Get the maximum retries from the client
     */
    protected function getMaxRetries(): int
    {
        return $this->client->getMaxRetries();
    }

    /**
     * Get custom headers from the client
     *
     * @return array<string, string>
     */
    protected function getCustomHeaders(): array
    {
        return $this->client->getCustomHeaders();
    }

    /**
     * Make a GET request to the API.
     *
     * @param array<string, mixed>|null $query Query parameters
     */
    protected function _get(string $path, ?array $query = null): mixed
    {
        $url = $this->getBaseUrl() . $path;
        return $this->makeRequest('GET', $url, $query ?? []);
    }

    /**
     * Make a POST request to the API.
     *
     * @param array<string, mixed>|null $body Request body
     * @param array<string, string> $additionalHeaders Extra headers for this request
     */
    protected function _post(string $path, ?array $body = null, array $additionalHeaders = []): mixed
    {
        $url = $this->getBaseUrl() . $path;
        return $this->makeRequest('POST', $url, $body ?? [], $additionalHeaders);
    }

    /**
     * Make a DELETE request to the API.
     */
    protected function _delete(string $path): mixed
    {
        $url = $this->getBaseUrl() . $path;
        return $this->makeRequest('DELETE', $url);
    }

    /**
     * Make an HTTP request using the client's HTTP client.
     *
     * @param string $method HTTP method (GET, POST, DELETE, etc.)
     * @param string $url Full URL
     * @param array<string, mixed>|null $params Parameters (query for GET, body for POST)
     * @param array<string, string> $additionalHeaders Extra headers for this request
     */
    protected function makeRequest(string $method, string $url, ?array $params = null, array $additionalHeaders = []): mixed
    {
        $transport = $this->client->getHttpTransport();
        $headers = array_merge($this->getCustomHeaders(), $additionalHeaders);

        return match (strtoupper($method)) {
            'GET' => $transport->get($url, $params ?? [], $headers),
            'POST' => $transport->post($url, $params ?? [], $headers),
            'PATCH' => $transport->patch($url, $params ?? [], $headers),
            'DELETE' => $transport->delete($url, $headers),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };
    }

    /**
     * Make a POST request that expects a streaming response.
     *
     * @param array<string, string> $additionalHeaders Extra headers for this request
     */
    protected function _postStream(string $path, mixed $body, array $additionalHeaders = []): StreamResponse
    {
        $url = $this->getBaseUrl() . $path;
        $transport = $this->client->getHttpTransport();
        $headers = array_merge($this->getCustomHeaders(), $additionalHeaders);
        $response = $transport->postStream($url, $body ?? [], $headers);

        return new StreamResponse($response);
    }

    /**
     * Get an asynchronous proxy for this resource.
     */
    public function async(): AsyncResourceProxy
    {
        return new AsyncResourceProxy($this);
    }
}
