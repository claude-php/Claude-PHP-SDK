<?php

declare(strict_types=1);

namespace ClaudePhp\Client;

use ClaudePhp\Exceptions\APIConnectionError;
use ClaudePhp\Exceptions\APIResponseValidationError;
use ClaudePhp\Exceptions\APIStatusError;
use ClaudePhp\Exceptions\AuthenticationError;
use ClaudePhp\Exceptions\BadRequestError;
use ClaudePhp\Exceptions\ConflictError;
use ClaudePhp\Exceptions\DeadlineExceededError;
use ClaudePhp\Exceptions\InternalServerError;
use ClaudePhp\Exceptions\NotFoundError;
use ClaudePhp\Exceptions\OverloadedError;
use ClaudePhp\Exceptions\PermissionDeniedError;
use ClaudePhp\Exceptions\RateLimitError;
use ClaudePhp\Exceptions\RequestTooLargeError;
use ClaudePhp\Exceptions\ServiceUnavailableError;
use ClaudePhp\Exceptions\UnprocessableEntityError;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * HTTP Client wrapper for making API requests.
 *
 * Wraps PSR-18 ClientInterface to provide convenient methods for
 * GET, POST, DELETE, and other HTTP operations with consistent
 * JSON encoding/decoding and error handling.
 */
class HttpClient
{
    /**
     * @var array<int, class-string<APIStatusError>>
     */
    private const STATUS_EXCEPTION_MAP = [
        400 => BadRequestError::class,
        401 => AuthenticationError::class,
        403 => PermissionDeniedError::class,
        404 => NotFoundError::class,
        409 => ConflictError::class,
        413 => RequestTooLargeError::class,
        422 => UnprocessableEntityError::class,
        429 => RateLimitError::class,
        500 => InternalServerError::class,
        503 => ServiceUnavailableError::class,
        504 => DeadlineExceededError::class,
        529 => OverloadedError::class,
    ];

    public function __construct(
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private array $defaultHeaders = [],
        private readonly float $timeout = 30.0,
    ) {
    }

    /**
     * Make a GET request.
     *
     * @param array<string, mixed> $query
     */
    public function get(string $url, array $query = [], array $headers = []): mixed
    {
        if ([] !== $query) {
            $url .= '?' . http_build_query($query);
        }

        $request = $this->requestFactory->createRequest('GET', $url);
        $response = $this->sendRequest($request, $headers);

        return $this->decodeJsonResponse($response);
    }

    /**
     * Make a GET request and return the raw PSR-7 response.
     *
     * Used for endpoints like batch results that stream non-JSON payloads
     * (e.g. JSONL) where the caller needs to incrementally consume the body.
     *
     * @param array<string, mixed> $query
     */
    public function getRaw(string $url, array $query = [], array $headers = []): ResponseInterface
    {
        if ([] !== $query) {
            $url .= '?' . http_build_query($query);
        }

        $request = $this->requestFactory->createRequest('GET', $url);

        return $this->sendRequest($request, $headers);
    }

    /**
     * Make a POST request with JSON body.
     *
     * @param array<string, mixed>|string $body
     */
    public function post(string $url, array|string $body = [], array $headers = []): mixed
    {
        $bodyString = \is_array($body) ? $this->encodeJson($body) : $body;
        $request = $this->requestFactory
            ->createRequest('POST', $url)
            ->withBody($this->streamFactory->createStream($bodyString))
        ;

        $response = $this->sendRequest($request, $headers);

        return $this->decodeJsonResponse($response);
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $url, array $headers = []): mixed
    {
        $request = $this->requestFactory->createRequest('DELETE', $url);
        $response = $this->sendRequest($request, $headers);

        return $this->decodeJsonResponse($response);
    }

    /**
     * Make a PATCH request with JSON body.
     *
     * @param array<string, mixed>|string $body
     */
    public function patch(string $url, array|string $body = [], array $headers = []): mixed
    {
        $bodyString = \is_array($body) ? $this->encodeJson($body) : $body;

        $request = $this->requestFactory
            ->createRequest('PATCH', $url)
            ->withBody($this->streamFactory->createStream($bodyString))
        ;

        $response = $this->sendRequest($request, $headers);

        return $this->decodeJsonResponse($response);
    }

    /**
     * Make a POST request that expects a streaming response.
     *
     * @param array<string, mixed>|string $body
     */
    public function postStream(string $url, array|string $body = [], array $headers = []): ResponseInterface
    {
        $bodyString = \is_array($body) ? $this->encodeJson($body) : $body;
        $request = $this->requestFactory
            ->createRequest('POST', $url)
            ->withBody($this->streamFactory->createStream($bodyString))
        ;

        $streamHeaders = array_merge([
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ], $headers);

        return $this->sendRequest($request, $streamHeaders);
    }

    /**
     * Send the HTTP request and return the raw response.
     *
     * @param array<string, string> $headers
     */
    private function sendRequest(RequestInterface $request, array $headers = []): ResponseInterface
    {
        $request = $request
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', $headers['Accept'] ?? 'application/json')
        ;

        foreach ($this->defaultHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new APIConnectionError('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() >= 400) {
            $this->handleErrorResponse($request, $response);
        }

        return $response;
    }

    /**
     * Decode a JSON response body.
     */
    private function decodeJsonResponse(ResponseInterface $response): mixed
    {
        $raw = (string) $response->getBody();
        if ('' === $raw) {
            return null;
        }

        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new APIResponseValidationError('Failed to decode API response: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Encode an array payload as JSON.
     *
     * @param array<string, mixed> $payload
     */
    private function encodeJson(array $payload): string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new APIResponseValidationError('Failed to encode request body: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Handle non-successful responses and throw the mapped exception.
     */
    private function handleErrorResponse(RequestInterface $request, ResponseInterface $response): void
    {
        $status = $response->getStatusCode();
        $body = $this->tryDecodeBody($response);
        $requestId = $response->getHeaderLine('request-id') ?: null;

        $message = 'HTTP ' . $status;
        if (\is_array($body)) {
            $message = $body['error']['message'] ?? $body['message'] ?? $message;
        } elseif (\is_string($body) && '' !== $body) {
            $message = $body;
        }

        $exceptionClass = self::STATUS_EXCEPTION_MAP[$status] ?? APIStatusError::class;

        throw new $exceptionClass($status, $message, $request, $response, $body, $requestId);
    }

    private function tryDecodeBody(ResponseInterface $response): array|object|string|null
    {
        $raw = (string) $response->getBody();
        if ('' === $raw) {
            return null;
        }

        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $raw;
        }
    }
}
