#!/usr/bin/env php
<?php
/**
 * Authentication Flexibility Examples
 *
 * Demonstrates the flexible authentication options available in the SDK,
 * including API keys, custom headers, Bearer tokens, and more.
 *
 * Based on Python SDK v0.76.0 auth header validation improvements.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

echo "=== Authentication Flexibility Examples ===\n\n";

// Example 1: Traditional API key authentication
echo "Example 1: Traditional API Key Authentication\n";
echo "----------------------------------------------\n\n";

try {
    $client = new ClaudePhp(
        apiKey: $_ENV['ANTHROPIC_API_KEY'] ?? 'sk-ant-your-key-here'
    );
    echo "✓ Client created with API key\n\n";
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n\n";
}

echo str_repeat("=", 80) . "\n\n";

// Example 2: Custom x-api-key header (useful for proxies)
echo "Example 2: Custom x-api-key Header\n";
echo "------------------------------------\n\n";

echo "When using a proxy or middleware that handles authentication:\n\n";

echo "```php\n";
echo "\$client = new ClaudePhp(\n";
echo "    apiKey: null,  // No API key needed\n";
echo "    customHeaders: [\n";
echo "        'x-api-key' => 'your-proxy-api-key',\n";
echo "    ]\n";
echo ");\n";
echo "```\n\n";

echo "This is useful for:\n";
echo "  • API gateways that manage keys centrally\n";
echo "  • Development proxies with custom authentication\n";
echo "  • Multi-tenant systems with key rotation\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 3: Bearer token authentication
echo "Example 3: Bearer Token Authentication\n";
echo "---------------------------------------\n\n";

echo "For OAuth2 or service account scenarios:\n\n";

echo "```php\n";
echo "\$client = new ClaudePhp(\n";
echo "    apiKey: null,\n";
echo "    customHeaders: [\n";
echo "        'Authorization' => 'Bearer your-oauth-token',\n";
echo "    ]\n";
echo ");\n";
echo "```\n\n";

echo "Use cases:\n";
echo "  • Service-to-service authentication\n";
echo "  • Temporary access tokens\n";
echo "  • OAuth2-based integrations\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 4: Azure AD or other custom auth
echo "Example 4: Azure AD or Custom Authentication\n";
echo "---------------------------------------------\n\n";

echo "For enterprise authentication systems:\n\n";

echo "```php\n";
echo "// Get token from your auth provider\n";
echo "\$azureToken = getAzureAdToken();\n\n";
echo "\$client = new ClaudePhp(\n";
echo "    apiKey: null,\n";
echo "    customHeaders: [\n";
echo "        'Authorization' => \"Bearer {\$azureToken}\",\n";
echo "        'X-Tenant-ID' => 'your-tenant-id',\n";
echo "    ]\n";
echo ");\n";
echo "```\n\n";

echo "Perfect for:\n";
echo "  • Azure AD integration\n";
echo "  • SAML/SSO environments\n";
echo "  • Custom IAM solutions\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 5: Both API key and custom headers
echo "Example 5: Combined Authentication\n";
echo "-----------------------------------\n\n";

echo "You can combine API key with additional headers:\n\n";

echo "```php\n";
echo "\$client = new ClaudePhp(\n";
echo "    apiKey: 'sk-ant-your-key',\n";
echo "    customHeaders: [\n";
echo "        'X-Request-ID' => 'unique-request-id',\n";
echo "        'X-Organization-ID' => 'org-12345',\n";
echo "    ]\n";
echo ");\n";
echo "```\n\n";

echo "Useful for:\n";
echo "  • Multi-tenant applications\n";
echo "  • Request tracing and monitoring\n";
echo "  • Organization-level access control\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 6: Environment-based authentication
echo "Example 6: Environment-Based Configuration\n";
echo "-------------------------------------------\n\n";

echo "Configure authentication based on environment:\n\n";

echo "```php\n";
echo "\$isProduction = getenv('APP_ENV') === 'production';\n\n";
echo "\$client = new ClaudePhp(\n";
echo "    apiKey: \$isProduction ? getenv('ANTHROPIC_API_KEY') : null,\n";
echo "    customHeaders: \$isProduction ? [] : [\n";
echo "        'x-api-key' => getenv('DEV_PROXY_KEY'),\n";
echo "    ]\n";
echo ");\n";
echo "```\n\n";

echo "This pattern allows:\n";
echo "  • Different auth for dev/staging/prod\n";
echo "  • Easy local development with proxies\n";
echo "  • Secure credential management\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 7: Error handling
echo "Example 7: Authentication Error Handling\n";
echo "-----------------------------------------\n\n";

echo "The SDK validates that authentication is provided:\n\n";

try {
    // This will fail - no authentication provided
    $client = new ClaudePhp(
        apiKey: null,
        customHeaders: []
    );
} catch (\InvalidArgumentException $e) {
    echo "✓ Expected error caught:\n";
    echo "  {$e->getMessage()}\n\n";
}

echo "Always provide either:\n";
echo "  • An API key (via parameter or ANTHROPIC_API_KEY env var)\n";
echo "  • Custom authentication headers (x-api-key, Authorization, etc.)\n\n";

echo str_repeat("=", 80) . "\n\n";

echo "✓ Authentication flexibility examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• API key authentication is still the default and recommended method\n";
echo "• Custom auth headers enable proxy, OAuth2, and enterprise scenarios\n";
echo "• Either API key OR custom auth headers must be provided\n";
echo "• Multiple authentication strategies can be combined\n";
echo "• Perfect for multi-tenant and enterprise deployments\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related documentation:\n";
echo "  • README.md - Configuration section\n";
echo "  • CHANGELOG.md - v0.5.2 authentication changes\n";
echo "  • docs/authentication.md - Detailed auth guide\n";
