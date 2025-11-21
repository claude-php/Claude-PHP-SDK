<?php

declare(strict_types=1);

namespace ClaudePhp\Tests;

use ClaudePhp\Exceptions\AnthropicException;
use ClaudePhp\Exceptions\APIConnectionError;
use ClaudePhp\Exceptions\APIError;
use ClaudePhp\Exceptions\APIStatusError;
use ClaudePhp\Exceptions\APITimeoutError;
use ClaudePhp\Exceptions\AuthenticationError;
use ClaudePhp\Exceptions\RateLimitError;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests for exception hierarchy
 */
class ExceptionHierarchyTest extends TestCase
{
    /**
     * Test that exception hierarchy is correct
     */
    public function testExceptionInheritance(): void
    {
        // Test base exception
        $e = new AnthropicException('test');
        $this->assertInstanceOf(\Exception::class, $e);

        // Test API error
        $e = new APIError('test');
        $this->assertInstanceOf(AnthropicException::class, $e);

        // Test connection error
        $e = new APIConnectionError('test');
        $this->assertInstanceOf(APIError::class, $e);

        // Test timeout error
        $e = new APITimeoutError('test');
        $this->assertInstanceOf(APIConnectionError::class, $e);

        // Test authentication error
        $e = new AuthenticationError(401, 'Unauthorized', $this->createMockRequest(), $this->createMockResponse());
        $this->assertInstanceOf(APIStatusError::class, $e);

        // Test rate limit error
        $e = new RateLimitError(429, 'Too Many Requests', $this->createMockRequest(), $this->createMockResponse());
        $this->assertInstanceOf(APIStatusError::class, $e);
    }

    /**
     * Test that APIStatusError stores response information
     */
    public function testAPIStatusErrorStoresResponseInfo(): void
    {
        $statusCode = 401;
        $message = 'Unauthorized';
        $request = $this->createMockRequest();
        $response = $this->createMockResponse();
        $body = ['error' => 'invalid_api_key'];
        $requestId = 'req-123';

        $e = new APIStatusError(
            $statusCode,
            $message,
            $request,
            $response,
            $body,
            $requestId,
        );

        $this->assertEquals($statusCode, $e->status_code);
        $this->assertEquals($message, $e->getMessage());
        $this->assertEquals($body, $e->body);
        $this->assertEquals($requestId, $e->request_id);
        $this->assertSame($request, $e->request);
        $this->assertSame($response, $e->response);
    }

    /**
     * Create a mock PSR-7 request
     */
    private function createMockRequest()
    {
        return $this->createMock(RequestInterface::class);
    }

    /**
     * Create a mock PSR-7 response
     */
    private function createMockResponse()
    {
        return $this->createMock(ResponseInterface::class);
    }
}
