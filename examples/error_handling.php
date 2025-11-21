#!/usr/bin/env php
<?php
/**
 * Error Handling Examples
 * 
 * Demonstrates proper error handling with the Claude PHP SDK,
 * matching Python SDK patterns for exception handling.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\AnthropicException;
use ClaudePhp\Exceptions\APIError;
use ClaudePhp\Exceptions\AuthenticationError;
use ClaudePhp\Exceptions\RateLimitError;
use ClaudePhp\Exceptions\APIConnectionError;
use ClaudePhp\Exceptions\APITimeoutError;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Error Handling Examples ===\n\n";

// Example 1: Basic try-catch
echo "Example 1: Basic Error Handling\n";
echo "--------------------------------\n";
try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, Claude!']
        ]
    ]);
    
    echo "Success! Response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
} catch (AnthropicException $e) {
    echo "Error occurred: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Specific exception types
echo "Example 2: Handling Specific Error Types\n";
echo "-----------------------------------------\n";
try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 100,
        'messages' => [
            ['role' => 'user', 'content' => 'What is PHP?']
        ]
    ]);
    
    echo "Response received successfully.\n";
} catch (AuthenticationError $e) {
    echo "Authentication failed: {$e->getMessage()}\n";
    echo "Please check your API key.\n";
} catch (RateLimitError $e) {
    echo "Rate limit exceeded: {$e->getMessage()}\n";
    echo "Please wait before making more requests.\n";
} catch (APIConnectionError $e) {
    echo "Connection error: {$e->getMessage()}\n";
    echo "Please check your network connection.\n";
} catch (APITimeoutError $e) {
    echo "Request timed out: {$e->getMessage()}\n";
    echo "Please try again.\n";
} catch (APIError $e) {
    echo "API error: {$e->getMessage()}\n";
} catch (AnthropicException $e) {
    echo "Unexpected error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Validation errors
echo "Example 3: Handling Invalid Parameters\n";
echo "---------------------------------------\n";
try {
    // This will fail because max_tokens is required
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!']
        ]
        // Missing max_tokens - will cause an error
    ]);
} catch (\InvalidArgumentException $e) {
    echo "✓ Validation error caught correctly\n";
    echo "Error: {$e->getMessage()}\n";
    echo "Remember to include required parameters!\n";
} catch (AnthropicException $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Testing with invalid API key
echo "Example 4: Invalid API Key Handling\n";
echo "------------------------------------\n";
try {
    $invalidClient = new ClaudePhp(apiKey: 'invalid-key-for-testing');
    $response = $invalidClient->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 100,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello']
        ]
    ]);
} catch (AuthenticationError $e) {
    echo "✓ Authentication error caught correctly\n";
    echo "Error message: {$e->getMessage()}\n";
} catch (AnthropicException $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Retry logic
echo "Example 5: Retry Logic Pattern\n";
echo "-------------------------------\n";

function makeRequestWithRetry($client, $maxRetries = 3) {
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        try {
            $response = $client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 100,
                'messages' => [
                    ['role' => 'user', 'content' => 'What is 2+2?']
                ]
            ]);
            
            return $response;
        } catch (RateLimitError $e) {
            $attempt++;
            if ($attempt < $maxRetries) {
                $waitTime = pow(2, $attempt); // Exponential backoff
                echo "Rate limited. Waiting {$waitTime} seconds before retry {$attempt}...\n";
                sleep($waitTime);
            } else {
                throw $e;
            }
        } catch (APIConnectionError $e) {
            $attempt++;
            if ($attempt < $maxRetries) {
                echo "Connection error. Retrying attempt {$attempt}...\n";
                sleep(1);
            } else {
                throw $e;
            }
        }
    }
    
    throw new Exception("Max retries exceeded");
}

try {
    $response = makeRequestWithRetry($client);
    echo "✓ Request successful with retry logic\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response: {$block['text']}\n";
        }
    }
} catch (Exception $e) {
    echo "Failed after retries: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Error handling examples completed!\n";

