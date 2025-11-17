<?php

declare(strict_types=1);

namespace ClaudePhp\Contracts;

use Psr\Http\Client\ClientInterface;

/**
 * Contract for HTTP client factory
 *
 * Provides methods to create PSR-18 HTTP clients
 */
interface HttpClientFactoryInterface
{
    /**
     * Create a PSR-18 HTTP client
     */
    public function createHttpClient(): ClientInterface;
}
