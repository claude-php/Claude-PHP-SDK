<?php

declare(strict_types=1);

namespace ClaudePhp\Tests;

use ClaudePhp\ClaudePhp;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Enhanced base test case for all SDK tests
 * Provides utilities equivalent to Python SDK's test infrastructure
 */
class TestCase extends PHPUnitTestCase
{
    protected array $httpHistory = [];
    protected ?MockHandler $mockHandler = null;
    protected ?ClaudePhp $testClient = null;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->httpHistory = [];
        $this->setupMockClient();
    }

    /**
     * Clean up test environment
     */
    protected function tearDown(): void
    {
        $this->httpHistory = [];
        $this->mockHandler = null;
        $this->testClient = null;
        parent::tearDown();
    }

    /**
     * Create a mock HTTP client for testing
     */
    protected function setupMockClient(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);

        // Add history middleware to track requests
        $history = Middleware::history($this->httpHistory);
        $handlerStack->push($history);

        $httpClient = new Client(['handler' => $handlerStack]);

        $this->testClient = new ClaudePhp(
            apiKey: TestUtils::getTestApiKey(),
            baseUrl: TestUtils::getTestBaseUrl(),
            httpClient: $httpClient,
        );
    }

    /**
     * Add a mock HTTP response to the queue
     *
     * @param int $status HTTP status code
     * @param array<string, string> $headers Response headers
     * @param string $body Response body
     */
    protected function addMockResponse(int $status = 200, array $headers = [], string $body = ''): void
    {
        if (!$this->mockHandler) {
            $this->setupMockClient();
        }

        $this->mockHandler->append(new Response($status, $headers, $body));
    }

    /**
     * Add multiple mock responses
     *
     * @param array<array{status?: int, headers?: array<string, string>, body?: string}> $responses
     */
    protected function addMockResponses(array $responses): void
    {
        foreach ($responses as $response) {
            $this->addMockResponse(
                $response['status'] ?? 200,
                $response['headers'] ?? [],
                $response['body'] ?? '',
            );
        }
    }

    /**
     * Get the last HTTP request made
     */
    protected function getLastRequest(): ?RequestInterface
    {
        if (empty($this->httpHistory)) {
            return null;
        }

        return $this->httpHistory[count($this->httpHistory) - 1]['request'];
    }

    /**
     * Get all HTTP requests made
     *
     * @return array<RequestInterface>
     */
    protected function getAllRequests(): array
    {
        return array_map(fn ($entry) => $entry['request'], $this->httpHistory);
    }

    /**
     * Assert that a specific HTTP request was made
     *
     * @param string $method Expected HTTP method
     * @param string $uri Expected URI path
     * @param array<string, mixed> $expectedBody Expected request body (optional)
     */
    protected function assertHttpRequestMade(string $method, string $uri, array $expectedBody = []): void
    {
        $lastRequest = $this->getLastRequest();
        $this->assertNotNull($lastRequest, 'No HTTP request was made');

        $this->assertEquals($method, $lastRequest->getMethod());
        $this->assertStringContainsString($uri, (string) $lastRequest->getUri());

        if (!empty($expectedBody)) {
            $actualBody = json_decode((string) $lastRequest->getBody(), true);
            $this->assertEquals($expectedBody, $actualBody);
        }
    }

    /**
     * Assert that the request contains specific headers
     *
     * @param array<string, string> $expectedHeaders Expected headers
     */
    protected function assertHttpHeadersPresent(array $expectedHeaders): void
    {
        $lastRequest = $this->getLastRequest();
        $this->assertNotNull($lastRequest, 'No HTTP request was made');

        foreach ($expectedHeaders as $header => $value) {
            $this->assertTrue($lastRequest->hasHeader($header));
            $this->assertEquals($value, $lastRequest->getHeaderLine($header));
        }
    }

    /**
     * Create a successful message response
     *
     * @param string $content The message content
     * @param string $model The model used
     *
     * @return string JSON response
     */
    protected function createMessageResponse(string $content = 'Hello!', string $model = 'claude-sonnet-4-5-20250929'): string
    {
        return json_encode([
            'id' => 'msg_test_' . uniqid(),
            'type' => 'message',
            'role' => 'assistant',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $content,
                ],
            ],
            'model' => $model,
            'stop_reason' => 'end_turn',
            'stop_sequence' => null,
            'usage' => [
                'input_tokens' => 10,
                'output_tokens' => 20,
            ],
        ]);
    }

    /**
     * Create a streaming message event
     *
     * @param string $event_type Event type
     * @param array<string, mixed> $data Event data
     *
     * @return string SSE formatted event
     */
    protected function createStreamingEvent(string $event_type, array $data): string
    {
        $json_data = json_encode($data);

        return "event: {$event_type}\ndata: {$json_data}\n\n";
    }

    /**
     * Create an error response
     *
     * @param string $message Error message
     * @param string $type Error type
     * @param int $status HTTP status code
     *
     * @return string JSON error response
     */
    protected function createErrorResponse(string $message = 'Test error', string $type = 'invalid_request_error', int $status = 400): string
    {
        return json_encode([
            'type' => 'error',
            'error' => [
                'type' => $type,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Assert that a value matches expected type using TestUtils
     */
    protected function assertMatchesType(mixed $expectedType, mixed $actualValue, string $message = ''): void
    {
        TestUtils::assertMatchesType($expectedType, $actualValue, $message);
    }

    /**
     * Assert that an object matches expected model structure using TestUtils
     */
    protected function assertMatchesModel(object $object, array $expectedProperties, string $message = ''): void
    {
        TestUtils::assertMatchesModel($object, $expectedProperties, $message);
    }

    /**
     * Update environment variables with automatic cleanup
     */
    protected function updateEnv(array $vars): \Closure
    {
        return TestUtils::updateEnv($vars);
    }
}
