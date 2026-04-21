<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Exceptions;

use ClaudePhp\Exceptions\APIStatusError;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class APIStatusErrorTest extends TestCase
{
    public function testExtractsErrorType(): void
    {
        $factory = new Psr17Factory();
        $request = $factory->createRequest('POST', 'https://api.anthropic.com/v1/messages');
        $response = $factory->createResponse(400);

        $body = [
            'error' => [
                'type' => 'invalid_request_error',
                'message' => 'Bad request',
            ],
        ];

        $error = new APIStatusError(400, 'Bad request', $request, $response, $body);

        $this->assertSame('invalid_request_error', $error->type);
        $this->assertSame('invalid_request_error', $error->getType());
    }

    public function testNullTypeWhenNoErrorBody(): void
    {
        $factory = new Psr17Factory();
        $request = $factory->createRequest('POST', 'https://api.anthropic.com/v1/messages');
        $response = $factory->createResponse(500);

        $error = new APIStatusError(500, 'Server error', $request, $response, 'raw body');

        $this->assertNull($error->type);
        $this->assertNull($error->getType());
    }

    public function testNullTypeWhenBodyIsNull(): void
    {
        $factory = new Psr17Factory();
        $request = $factory->createRequest('POST', 'https://api.anthropic.com/v1/messages');
        $response = $factory->createResponse(500);

        $error = new APIStatusError(500, 'Error', $request, $response);

        $this->assertNull($error->type);
    }
}
