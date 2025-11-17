<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

// This file serves as a central index for all Beta types
// Export all beta types from this module

class Index
{
    public const CLASSES = [
        // Message types
        'BetaMessage' => BetaMessage::class,
        'BetaUsage' => BetaUsage::class,
        'BetaMessageTokensCount' => BetaMessageTokensCount::class,

        // Content block types
        'BetaTextBlock' => BetaTextBlock::class,
        'BetaThinkingBlock' => BetaThinkingBlock::class,
        'BetaRedactedThinkingBlock' => BetaRedactedThinkingBlock::class,
        'BetaToolUseBlock' => BetaToolUseBlock::class,
        'BetaServerToolUseBlock' => BetaServerToolUseBlock::class,
        'BetaWebSearchToolResultBlock' => BetaWebSearchToolResultBlock::class,

        // Delta types
        'BetaTextDelta' => BetaTextDelta::class,
        'BetaThinkingDelta' => BetaThinkingDelta::class,
        'BetaInputJSONDelta' => BetaInputJSONDelta::class,
        'BetaCitationsDelta' => BetaCitationsDelta::class,
        'BetaSignatureDelta' => BetaSignatureDelta::class,

        // Image/PDF sources
        'BetaBase64ImageSource' => BetaBase64ImageSource::class,
        'BetaURLImageSource' => BetaURLImageSource::class,
        'BetaBase64PDFSource' => BetaBase64PDFSource::class,
        'BetaURLPDFSource' => BetaURLPDFSource::class,
        'BetaPlainTextSource' => BetaPlainTextSource::class,

        // File types
        'FileMetadata' => FileMetadata::class,
        'DeletedFile' => DeletedFile::class,

        // Cache types
        'BetaCacheCreation' => BetaCacheCreation::class,
        'BetaCacheControlEphemeral' => BetaCacheControlEphemeral::class,

        // Error types
        'BetaAPIError' => BetaAPIError::class,
        'BetaAuthenticationError' => BetaAuthenticationError::class,
        'BetaBillingError' => BetaBillingError::class,
        'BetaPermissionError' => BetaPermissionError::class,
        'BetaNotFoundError' => BetaNotFoundError::class,
        'BetaRateLimitError' => BetaRateLimitError::class,
        'BetaInvalidRequestError' => BetaInvalidRequestError::class,
        'BetaGatewayTimeoutError' => BetaGatewayTimeoutError::class,
        'BetaOverloadedError' => BetaOverloadedError::class,
        'BetaErrorResponse' => BetaErrorResponse::class,

        // Code execution types
        'BetaBashCodeExecutionOutputBlock' => BetaBashCodeExecutionOutputBlock::class,
        'BetaBashCodeExecutionOutputBlockParam' => BetaBashCodeExecutionOutputBlockParam::class,
        'BetaBashCodeExecutionResultBlock' => BetaBashCodeExecutionResultBlock::class,
        'BetaBashCodeExecutionResultBlockParam' => BetaBashCodeExecutionResultBlockParam::class,
        'BetaBashCodeExecutionToolResultBlock' => BetaBashCodeExecutionToolResultBlock::class,
        'BetaBashCodeExecutionToolResultBlockParam' => BetaBashCodeExecutionToolResultBlockParam::class,
        'BetaBashCodeExecutionToolResultError' => BetaBashCodeExecutionToolResultError::class,
        'BetaBashCodeExecutionToolResultErrorParam' => BetaBashCodeExecutionToolResultErrorParam::class,

        // Advanced Beta features
        'BetaClearThinking20251015EditParam' => BetaClearThinking20251015EditParam::class,
        'BetaClearThinking20251015EditResponse' => BetaClearThinking20251015EditResponse::class,
        'BetaClearToolUses20250919EditParam' => BetaClearToolUses20250919EditParam::class,
        'BetaClearToolUses20250919EditResponse' => BetaClearToolUses20250919EditResponse::class,
        'BetaAllThinkingTurnsParam' => BetaAllThinkingTurnsParam::class,

        // Additional Beta source types
        'BetaBase64ImageSourceParam' => BetaBase64ImageSourceParam::class,
        'BetaBase64PDFBlockParam' => BetaBase64PDFBlockParam::class,
        'BetaBase64PDFSourceParam' => BetaBase64PDFSourceParam::class,
    ];
}
