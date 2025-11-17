<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

// Shared error types
use ClaudePhp\Types\Shared\ErrorObject;
use ClaudePhp\Types\Shared\ErrorResponse;
use ClaudePhp\Types\Shared\BillingError;
use ClaudePhp\Types\Shared\AuthenticationError;
use ClaudePhp\Types\Shared\PermissionError;
use ClaudePhp\Types\Shared\NotFoundError;
use ClaudePhp\Types\Shared\RateLimitError;
use ClaudePhp\Types\Shared\InvalidRequestError;
use ClaudePhp\Types\Shared\GatewayTimeoutError;
use ClaudePhp\Types\Shared\OverloadedError;
use ClaudePhp\Types\Shared\APIErrorObject;

// Core types
use ClaudePhp\Types\Usage;
use ClaudePhp\Types\TextBlock;
use ClaudePhp\Types\ToolUseBlock;
use ClaudePhp\Types\ThinkingBlock;
use ClaudePhp\Types\RedactedThinkingBlock;
use ClaudePhp\Types\TextCitation;
use ClaudePhp\Types\CitationCharLocation;
use ClaudePhp\Types\CitationPageLocation;
use ClaudePhp\Types\CitationContentBlockLocation;

// Streaming types
use ClaudePhp\Types\TextDelta;
use ClaudePhp\Types\ThinkingDelta;
use ClaudePhp\Types\InputJSONDelta;
use ClaudePhp\Types\CitationsDelta;
use ClaudePhp\Types\SignatureDelta;
use ClaudePhp\Types\MessageDeltaUsage;

// Source/Image types
use ClaudePhp\Types\Base64ImageSource;
use ClaudePhp\Types\URLImageSource;
use ClaudePhp\Types\Base64PDFSource;
use ClaudePhp\Types\URLPDFSource;
use ClaudePhp\Types\PlainTextSource;

// Tool and cache types
use ClaudePhp\Types\Tool;
use ClaudePhp\Types\WebSearchTool20250305;
use ClaudePhp\Types\ToolBash20250124;
use ClaudePhp\Types\ToolTextEditor20250124;
use ClaudePhp\Types\ToolTextEditor20250429;
use ClaudePhp\Types\ToolTextEditor20250728;
use ClaudePhp\Types\CacheControlEphemeral;
use ClaudePhp\Types\CacheCreation;

// Tool choice types
use ClaudePhp\Types\ToolChoiceAuto;
use ClaudePhp\Types\ToolChoiceAny;
use ClaudePhp\Types\ToolChoiceNone;
use ClaudePhp\Types\ToolChoiceTool;

// Thinking config types
use ClaudePhp\Types\ThinkingConfigEnabled;
use ClaudePhp\Types\ThinkingConfigDisabled;

// Web search and server tool types
use ClaudePhp\Types\WebSearchToolResultBlock;
use ClaudePhp\Types\WebSearchToolResultError;
use ClaudePhp\Types\WebSearchResultBlock;
use ClaudePhp\Types\ServerToolUsage;
use ClaudePhp\Types\ServerToolUseBlock;

// Message and model types
use ClaudePhp\Types\Model;
use ClaudePhp\Types\ModelInfo;
use ClaudePhp\Types\StopReason;
use ClaudePhp\Types\Metadata;
use ClaudePhp\Types\MessageCountTokensTool;
use ClaudePhp\Types\MessageTokensCount;

// Event types
use ClaudePhp\Types\RawContentBlockDeltaEvent;
use ClaudePhp\Types\RawContentBlockStartEvent;
use ClaudePhp\Types\RawContentBlockStopEvent;
use ClaudePhp\Types\RawMessageStartEvent;
use ClaudePhp\Types\RawMessageDeltaEvent;
use ClaudePhp\Types\RawMessageStopEvent;
use ClaudePhp\Types\RawContentBlockDelta;
use ClaudePhp\Types\ContentBlockDeltaEvent;
use ClaudePhp\Types\ContentBlockStartEvent;
use ClaudePhp\Types\ContentBlockStopEvent;
use ClaudePhp\Types\MessageStreamEvent;

// Parameter types
use ClaudePhp\Types\TextBlockParam;
use ClaudePhp\Types\ImageBlockParam;
use ClaudePhp\Types\DocumentBlockParam;
use ClaudePhp\Types\ToolResultBlockParam;
use ClaudePhp\Types\ThinkingBlockParam;
use ClaudePhp\Types\RedactedThinkingBlockParam;
use ClaudePhp\Types\ToolUseBlockParam;
use ClaudePhp\Types\ServerToolUseBlockParam;
use ClaudePhp\Types\WebSearchResultBlockParam;
use ClaudePhp\Types\WebSearchToolResultBlockParam;

// Citation params
use ClaudePhp\Types\CitationCharLocationParam;
use ClaudePhp\Types\CitationPageLocationParam;
use ClaudePhp\Types\CitationContentBlockLocationParam;
use ClaudePhp\Types\CitationSearchResultLocationParam;
use ClaudePhp\Types\CitationWebSearchResultLocationParam;
use ClaudePhp\Types\CitationsConfigParam;
use ClaudePhp\Types\CacheControlEphemeralParam;
use ClaudePhp\Types\ThinkingConfigEnabledParam;
use ClaudePhp\Types\ThinkingConfigDisabledParam;
use ClaudePhp\Types\TextCitationParam;
use ClaudePhp\Types\AnthropicBeta;

// Message and completion response types
use ClaudePhp\Types\Message;
use ClaudePhp\Types\Completion;

// Message and completion request parameter types
use ClaudePhp\Types\MessageParam;
use ClaudePhp\Types\MessageCreateParams;
use ClaudePhp\Types\MessageCountTokensParams;
use ClaudePhp\Types\CompletionCreateParams;

// Tool Param variants
use ClaudePhp\Types\ToolBash20250124Param;
use ClaudePhp\Types\ToolTextEditor20250124Param;
use ClaudePhp\Types\ToolTextEditor20250429Param;
use ClaudePhp\Types\ToolTextEditor20250728Param;
use ClaudePhp\Types\WebSearchTool20250305Param;

// Source Param variants
use ClaudePhp\Types\Base64ImageSourceParam;
use ClaudePhp\Types\URLImageSourceParam;
use ClaudePhp\Types\Base64PDFSourceParam;
use ClaudePhp\Types\URLPDFSourceParam;
use ClaudePhp\Types\PlainTextSourceParam;

// Tool choice Param variants
use ClaudePhp\Types\ToolChoiceAutoParam;
use ClaudePhp\Types\ToolChoiceAnyParam;
use ClaudePhp\Types\ToolChoiceNoneParam;
use ClaudePhp\Types\ToolChoiceToolParam;

// Content block source types
use ClaudePhp\Types\ContentBlockSource;
use ClaudePhp\Types\ContentBlockSourceParam;
use ClaudePhp\Types\ContentBlock;

// Web search result block content types
use ClaudePhp\Types\WebSearchToolResultBlockContent;
use ClaudePhp\Types\WebSearchToolResultBlockContentParam;

// Citation location types
use ClaudePhp\Types\CitationsSearchResultLocation;
use ClaudePhp\Types\CitationsWebSearchResultLocation;

// Generic parameter types
use ClaudePhp\Types\ToolParam;
use ClaudePhp\Types\ModelParam;
use ClaudePhp\Types\MetadataParam;
use ClaudePhp\Types\SearchResultBlockParam;
use ClaudePhp\Types\MessageCountTokensToolParam;

// Messages subdirectory
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
