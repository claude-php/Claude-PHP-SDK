<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Shared;

// This file serves as a central index for all Shared error types
// Export all error types from this module

class Index
{
    public const CLASSES = [
        'ErrorObject' => ErrorObject::class,
        'ErrorResponse' => ErrorResponse::class,
        'BillingError' => BillingError::class,
        'AuthenticationError' => AuthenticationError::class,
        'PermissionError' => PermissionError::class,
        'NotFoundError' => NotFoundError::class,
        'RateLimitError' => RateLimitError::class,
        'InvalidRequestError' => InvalidRequestError::class,
        'GatewayTimeoutError' => GatewayTimeoutError::class,
        'OverloadedError' => OverloadedError::class,
        'APIErrorObject' => APIErrorObject::class,
    ];
}
