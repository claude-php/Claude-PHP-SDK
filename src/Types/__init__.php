<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

// Shared error types
use ClaudePhp\Types\Shared\APIErrorObject;
use ClaudePhp\Types\Shared\AuthenticationError;
use ClaudePhp\Types\Shared\BillingError;
use ClaudePhp\Types\Shared\ErrorObject;
use ClaudePhp\Types\Shared\ErrorResponse;
use ClaudePhp\Types\Shared\GatewayTimeoutError;
use ClaudePhp\Types\Shared\InvalidRequestError;
use ClaudePhp\Types\Shared\NotFoundError;
use ClaudePhp\Types\Shared\OverloadedError;
use ClaudePhp\Types\Shared\PermissionError;
use ClaudePhp\Types\Shared\RateLimitError;

// Core types// Streaming types// Source/Image types// Tool and cache types// Tool choice types// Thinking config types// Web search and server tool types// Message and model types// Event types// Parameter types// Citation params// Message and completion response types

// Message and completion request parameter types// Tool Param variants// Source Param variants// Tool choice Param variants// Content block source types// Web search result block content types// Citation location types// Generic parameter types// Messages subdirectory
require_once __DIR__ . '/Messages/__init__.php';

/**
 * All available types from the Anthropic API
 */
class Types
{
    // Error types
    public static function errorObject(): string
    {
        return ErrorObject::class;
    }

    public static function errorResponse(): string
    {
        return ErrorResponse::class;
    }

    public static function billingError(): string
    {
        return BillingError::class;
    }

    public static function authenticationError(): string
    {
        return AuthenticationError::class;
    }

    public static function permissionError(): string
    {
        return PermissionError::class;
    }

    public static function notFoundError(): string
    {
        return NotFoundError::class;
    }

    public static function rateLimitError(): string
    {
        return RateLimitError::class;
    }

    public static function invalidRequestError(): string
    {
        return InvalidRequestError::class;
    }

    public static function gatewayTimeoutError(): string
    {
        return GatewayTimeoutError::class;
    }

    public static function overloadedError(): string
    {
        return OverloadedError::class;
    }

    public static function apiErrorObject(): string
    {
        return APIErrorObject::class;
    }

    // Core types
    public static function usage(): string
    {
        return Usage::class;
    }

    public static function textBlock(): string
    {
        return TextBlock::class;
    }

    public static function toolUseBlock(): string
    {
        return ToolUseBlock::class;
    }

    public static function thinkingBlock(): string
    {
        return ThinkingBlock::class;
    }

    public static function redactedThinkingBlock(): string
    {
        return RedactedThinkingBlock::class;
    }

    public static function textCitation(): string
    {
        return TextCitation::class;
    }

    // Etc - add all types as needed
}
