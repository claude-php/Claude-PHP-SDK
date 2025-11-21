<?php

/**
 * Helper functions for examples
 */

/**
 * Load environment variables from .env file
 */
function loadEnv(string $envFile): void
{
    if (!file_exists($envFile)) {
        throw new RuntimeException(".env file not found at: {$envFile}");
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Skip malformed lines
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

/**
 * Get API key from environment
 * Supports both ANTHROPIC_API_KEY and ANTHROPIC_API_KEY
 */
function getApiKey(): string
{
    return $_ENV['ANTHROPIC_API_KEY'] ?? $_ENV['ANTHROPIC_API_KEY'] ?? throw new RuntimeException(
        'API key not found. Set ANTHROPIC_API_KEY or ANTHROPIC_API_KEY in .env file'
    );
}

/**
 * Create a ClaudePhp client with API key from environment
 */
function createClient(): ClaudePhp\ClaudePhp
{
    loadEnv(__DIR__ . '/../.env');
    return new ClaudePhp\ClaudePhp(apiKey: getApiKey());
}
