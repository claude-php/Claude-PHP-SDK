#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

// Load .env file manually
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

// SDK expects ANTHROPIC_API_KEY but .env has ANTHROPIC_API_KEY
$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? $_ENV['ANTHROPIC_API_KEY'] ?? null;

echo "API Key (first 10 chars): " . substr($apiKey, 0, 10) . "...\n";

$client = new ClaudePhp(apiKey: $apiKey);

echo "Base URL: " . $client->getBaseUrl() . "\n";
echo "Timeout: " . $client->getTimeout() . "\n";
echo "Max Retries: " . $client->getMaxRetries() . "\n";

echo "\nSending test message...\n";

try {
    $response = $client->messages()->create([
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Say "Hello" in one word.',
            ],
        ],
        'model' => 'claude-sonnet-4-5-20250929',
    ]);

    echo "Success! Response:\n";
    print_r($response);
} catch (\ClaudePhp\Exceptions\NotFoundError $e) {
    echo "NotFoundError: " . $e->getMessage() . "\n";

    // Use reflection to get the request property if it exists
    $reflection = new \ReflectionClass($e);
    if ($reflection->hasProperty('request')) {
        $requestProp = $reflection->getProperty('request');
        $requestProp->setAccessible(true);
        $request = $requestProp->getValue($e);
        if ($request) {
            echo "Request URL: " . $request->getUri() . "\n";
            echo "Request Method: " . $request->getMethod() . "\n";
            echo "Request Headers:\n";
            foreach ($request->getHeaders() as $name => $values) {
                echo "  $name: " . implode(', ', $values) . "\n";
            }
        }
    }

    if ($reflection->hasProperty('response')) {
        $responseProp = $reflection->getProperty('response');
        $responseProp->setAccessible(true);
        $response = $responseProp->getValue($e);
        if ($response) {
            echo "Response Status: " . $response->getStatusCode() . "\n";
            echo "Response Body: " . $response->getBody() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Class: " . get_class($e) . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
