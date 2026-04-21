<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Lib\Bedrock\AnthropicBedrockMantle;

// Bedrock Mantle provides a direct HTTP API to Claude on AWS Bedrock.
// It supports Bearer token authentication (no SigV4 required).
//
// Set environment variables:
//   AWS_BEARER_TOKEN_BEDROCK=your-token
//   AWS_REGION=us-east-1 (optional, defaults to us-east-1)
//
// Or pass credentials directly:

$mantle = new AnthropicBedrockMantle(
    apiKey: getenv('AWS_BEARER_TOKEN_BEDROCK') ?: 'your-bearer-token',
    region: getenv('AWS_REGION') ?: 'us-east-1',
);

echo "Mantle base URL: {$mantle->getBaseUrl()}\n";
echo "Region: {$mantle->getRegion()}\n";
echo "Auth headers: " . json_encode($mantle->authHeaders()) . "\n\n";

// Access the Beta Messages API via Mantle
$beta = $mantle->beta();
$messages = $beta->messages();

echo "Mantle client configured. To make API calls, ensure valid AWS credentials.\n";
echo "Example usage:\n";
echo "  \$response = \$mantle->beta()->messages()->create([\n";
echo "      'model' => 'claude-sonnet-4-6',\n";
echo "      'max_tokens' => 100,\n";
echo "      'messages' => [['role' => 'user', 'content' => 'Hello!']],\n";
echo "  ]);\n";
