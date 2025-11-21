<?php

declare(strict_types=1);

namespace ClaudePhp\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Exception thrown for 4xx and 5xx HTTP status codes
 */
class APIStatusError extends APIError
{
    /**
     * @var int
     */
    public $status_code;

    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * @var null|array<string, mixed>|object|string
     */
    public $body;

    /**
     * @var null|string
     */
    public $request_id;

    /**
     * @param int $status_code HTTP status code
     * @param string $message Error message
     * @param RequestInterface $request The HTTP request
     * @param ResponseInterface $response The HTTP response
     * @param null|array<string, mixed>|object|string $body Parsed response body
     * @param null|string $request_id Request ID from headers
     */
    public function __construct(
        int $status_code,
        string $message,
        RequestInterface $request,
        ResponseInterface $response,
        array|object|string|null $body = null,
        ?string $request_id = null,
    ) {
        parent::__construct($message);
        $this->status_code = $status_code;
        $this->request = $request;
        $this->response = $response;
        $this->body = $body;
        $this->request_id = $request_id;
    }
}
