<?php

/**
 * Microsoft Azure AI Foundry Integration Examples
 *
 * This example demonstrates how to use Claude through Microsoft's Azure AI Foundry
 * platform with both API key and Azure AD authentication methods.
 *
 * Azure AI Foundry provides enterprise-grade infrastructure for Claude models with:
 * - Azure security and compliance
 * - Regional deployment options
 * - Azure AD authentication
 * - Enterprise billing and monitoring
 *
 * Setup:
 * 1. Create an Azure AI Foundry resource in the Azure portal
 * 2. Get your resource name (e.g., "my-foundry-resource")
 * 3. Either:
 *    - Get an API key from the Foundry portal, OR
 *    - Configure Azure AD authentication
 *
 * References:
 * - https://aka.ms/foundry/claude/docs
 * - https://docs.claude.com/en/docs/build-with-claude/claude-in-microsoft-foundry
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Lib\Foundry\AnthropicFoundry;

// ============================================================================
// Example 1: Basic API Key Authentication
// ============================================================================

echo "Example 1: API Key Authentication\n";
echo str_repeat('=', 80) . "\n\n";

// Replace with your actual resource name and API key
$resourceName = $_ENV['AZURE_FOUNDRY_RESOURCE'] ?? 'your-resource-name';
$apiKey = $_ENV['AZURE_FOUNDRY_API_KEY'] ?? 'your-foundry-api-key';

try {
    $client = new AnthropicFoundry(
        resource: $resourceName,
        apiKey: $apiKey
    );

    $response = $client->createMessage([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello! Can you explain what Azure AI Foundry is?'
            ]
        ]
    ]);

    echo "Model: {$response->model}\n";
    echo "Stop Reason: {$response->stop_reason}\n";
    echo "Tokens - Input: {$response->usage->input_tokens}, Output: {$response->usage->output_tokens}\n\n";
    echo "Response:\n{$response->content[0]->text}\n\n";
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ============================================================================
// Example 2: Azure AD Token Authentication
// ============================================================================

echo "\nExample 2: Azure AD Token Authentication\n";
echo str_repeat('=', 80) . "\n\n";

/**
 * Azure AD token provider function.
 *
 * In production, you would use the Azure Identity SDK to get tokens:
 *
 * composer require microsoft/azure-identity
 *
 * use Azure\Identity\DefaultAzureCredential;
 *
 * $credential = new DefaultAzureCredential();
 * $token = $credential->getToken(['https://ai.azure.com/.default']);
 * return $token->getToken();
 */
function getAzureAdToken(): string
{
    // This is a placeholder - replace with actual Azure AD token acquisition
    // In production, use Azure\Identity\DefaultAzureCredential or similar

    // For demonstration purposes only:
    if (isset($_ENV['AZURE_AD_TOKEN'])) {
        return $_ENV['AZURE_AD_TOKEN'];
    }

    // In real usage, this would call Azure AD to get a token:
    // $credential = new DefaultAzureCredential();
    // $tokenResponse = $credential->getToken(['https://ai.azure.com/.default']);
    // return $tokenResponse->getToken();

    throw new \RuntimeException('Azure AD token not configured. Set AZURE_AD_TOKEN environment variable.');
}

try {
    $client = new AnthropicFoundry(
        resource: $resourceName,
        azureAdTokenProvider: fn() => getAzureAdToken()
    );

    $response = $client->createMessage([
        'model' => 'claude-haiku-4-5',
        'max_tokens' => 512,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What are the benefits of using Azure AD authentication?'
            ]
        ]
    ]);

    echo "Model: {$response->model}\n";
    echo "Response:\n{$response->content[0]->text}\n\n";
} catch (\Exception $e) {
    echo "Note: Azure AD authentication requires proper token setup.\n";
    echo "Error: {$e->getMessage()}\n\n";
}

// ============================================================================
// Example 3: Streaming Messages
// ============================================================================

echo "\nExample 3: Streaming Messages\n";
echo str_repeat('=', 80) . "\n\n";

try {
    $client = new AnthropicFoundry(
        resource: $resourceName,
        apiKey: $apiKey
    );

    echo "Streaming response:\n";

    foreach ($client->createMessageStream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Write a haiku about cloud computing.'
            ]
        ]
    ]) as $event) {
        if (($event['type'] ?? null) === 'content_block_delta') {
            echo $event['delta']['text'] ?? '';
        }
    }

    echo "\n\n";
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ============================================================================
// Example 4: Tool Use with Foundry
// ============================================================================

echo "\nExample 4: Tool Use\n";
echo str_repeat('=', 80) . "\n\n";

try {
    $client = new AnthropicFoundry(
        resource: $resourceName,
        apiKey: $apiKey
    );

    $response = $client->createMessage([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [
            [
                'name' => 'get_weather',
                'description' => 'Get the current weather for a location',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and state, e.g. San Francisco, CA'
                        ],
                        'unit' => [
                            'type' => 'string',
                            'enum' => ['celsius', 'fahrenheit'],
                            'description' => 'The unit of temperature'
                        ]
                    ],
                    'required' => ['location']
                ]
            ]
        ],
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What is the weather in San Francisco?'
            ]
        ]
    ]);

    echo "Model: {$response->model}\n";
    echo "Stop Reason: {$response->stop_reason}\n\n";

    foreach ($response->content as $block) {
        if ($block->type === 'tool_use') {
            echo "Tool Called: {$block->name}\n";
            echo "Tool ID: {$block->id}\n";
            echo "Arguments: " . json_encode($block->input, JSON_PRETTY_PRINT) . "\n\n";
        } elseif ($block->type === 'text') {
            echo "Text: {$block->text}\n\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ============================================================================
// Example 5: Token Counting
// ============================================================================

echo "\nExample 5: Token Counting\n";
echo str_repeat('=', 80) . "\n\n";

try {
    $client = new AnthropicFoundry(
        resource: $resourceName,
        apiKey: $apiKey
    );

    $count = $client->countTokens([
        'model' => 'claude-sonnet-4-5',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'This is a test message for counting tokens.'
            ]
        ]
    ]);

    echo "Input Tokens: {$count->input_tokens}\n\n";
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ============================================================================
// Example 6: Multi-turn Conversation
// ============================================================================

echo "\nExample 6: Multi-turn Conversation\n";
echo str_repeat('=', 80) . "\n\n";

try {
    $client = new AnthropicFoundry(
        resource: $resourceName,
        apiKey: $apiKey
    );

    $messages = [
        [
            'role' => 'user',
            'content' => 'My name is Alice.'
        ]
    ];

    // First turn
    $response = $client->createMessage([
        'model' => 'claude-haiku-4-5',
        'max_tokens' => 512,
        'messages' => $messages
    ]);

    echo "Turn 1:\n";
    echo "Assistant: {$response->content[0]->text}\n\n";

    // Add assistant response to conversation history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content[0]->text
    ];

    // Second turn
    $messages[] = [
        'role' => 'user',
        'content' => 'What is my name?'
    ];

    $response = $client->createMessage([
        'model' => 'claude-haiku-4-5',
        'max_tokens' => 512,
        'messages' => $messages
    ]);

    echo "Turn 2:\n";
    echo "Assistant: {$response->content[0]->text}\n\n";
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ============================================================================
// Example 7: Vision (Image Analysis)
// ============================================================================

echo "\nExample 7: Vision Support\n";
echo str_repeat('=', 80) . "\n\n";

try {
    $client = new AnthropicFoundry(
        resource: $resourceName,
        apiKey: $apiKey
    );

    // Load an image and convert to base64
    $imagePath = __DIR__ . '/logo.png';

    if (file_exists($imagePath)) {
        $imageData = base64_encode(file_get_contents($imagePath));
        $mediaType = 'image/png';

        $response = $client->createMessage([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mediaType,
                                'data' => $imageData
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => 'What do you see in this image?'
                        ]
                    ]
                ]
            ]
        ]);

        echo "Image Analysis:\n{$response->content[0]->text}\n\n";
    } else {
        echo "Note: logo.png not found. Skipping vision example.\n\n";
    }
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ============================================================================
// Example 8: Error Handling
// ============================================================================

echo "\nExample 8: Error Handling\n";
echo str_repeat('=', 80) . "\n\n";

try {
    // Intentionally create a client with invalid configuration
    $client = new AnthropicFoundry(
        resource: 'invalid-resource',
        apiKey: 'invalid-key'
    );

    $response = $client->createMessage([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello']
        ]
    ]);
} catch (\InvalidArgumentException $e) {
    echo "Configuration Error: {$e->getMessage()}\n\n";
} catch (\RuntimeException $e) {
    echo "Runtime Error: {$e->getMessage()}\n\n";
    echo "This is expected when using invalid credentials.\n\n";
} catch (\Exception $e) {
    echo "General Error: {$e->getMessage()}\n\n";
}

// ============================================================================
// Summary
// ============================================================================

echo "\nSummary\n";
echo str_repeat('=', 80) . "\n";
echo "Azure AI Foundry provides enterprise-grade Claude access with:\n";
echo "- API Key authentication for simple integration\n";
echo "- Azure AD authentication for enterprise security\n";
echo "- Full support for all Claude features (streaming, tools, vision)\n";
echo "- Azure compliance and regional deployment\n";
echo "- Enterprise billing and monitoring\n\n";

echo "To get started:\n";
echo "1. Create an Azure AI Foundry resource\n";
echo "2. Configure environment variables:\n";
echo "   export AZURE_FOUNDRY_RESOURCE='your-resource-name'\n";
echo "   export AZURE_FOUNDRY_API_KEY='your-api-key'\n";
echo "3. Run this example: php examples/foundry.php\n\n";

echo "For Azure AD authentication:\n";
echo "1. Install Azure Identity SDK: composer require microsoft/azure-identity\n";
echo "2. Configure DefaultAzureCredential or similar\n";
echo "3. Pass token provider to AnthropicFoundry constructor\n\n";

