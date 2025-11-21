<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Foundry;

/**
 * Foundry library - provides access to Claude via Microsoft Azure AI Foundry.
 *
 * This module provides clients for accessing Claude models through Microsoft's
 * Azure AI Foundry platform with support for both API key and Azure AD authentication.
 */

/**
 * Get the Foundry library metadata.
 *
 * @return array<string, mixed>
 */
function getFoundryLibrary(): array
{
    return [
        'name' => 'Foundry',
        'version' => '1.0.0',
        'description' => 'Access Claude via Microsoft Azure AI Foundry',
        'classes' => [
            AnthropicFoundry::class,
            AsyncAnthropicFoundry::class,
        ],
        'features' => [
            'api_key_auth' => 'API key authentication',
            'azure_ad_auth' => 'Azure AD token authentication',
            'streaming' => 'Streaming support',
            'async' => 'Async operations via Amphp',
        ],
    ];
}
