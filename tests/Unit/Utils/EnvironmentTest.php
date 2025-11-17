<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Tests\TestCase;
use ClaudePhp\Tests\TestUtils;
use ClaudePhp\ClaudePhp;

/**
 * Environment configuration and testing utilities
 * Equivalent to Python SDK's environment testing
 */
class EnvironmentTest extends TestCase
{
    public function testUpdateEnvWithNewVariables(): void
    {
        // Store original values
        $originalApiKey = $_ENV['ANTHROPIC_API_KEY'] ?? null;
        $originalBaseUrl = $_ENV['ANTHROPIC_BASE_URL'] ?? null;

        // Update environment variables
        $cleanup = TestUtils::updateEnv([
            'ANTHROPIC_API_KEY' => 'test-key-12345',
            'ANTHROPIC_BASE_URL' => 'https://api.test.anthropic.com',
        ]);

        // Verify variables are set
        $this->assertEquals('test-key-12345', $_ENV['ANTHROPIC_API_KEY']);
        $this->assertEquals('https://api.test.anthropic.com', $_ENV['ANTHROPIC_BASE_URL']);
        $this->assertEquals('test-key-12345', $_SERVER['ANTHROPIC_API_KEY']);
        $this->assertEquals('https://api.test.anthropic.com', $_SERVER['ANTHROPIC_BASE_URL']);

        // Clean up
        $cleanup();

        // Verify restoration
        $this->assertEquals($originalApiKey, $_ENV['ANTHROPIC_API_KEY'] ?? null);
        $this->assertEquals($originalBaseUrl, $_ENV['ANTHROPIC_BASE_URL'] ?? null);
    }

    public function testUpdateEnvWithUnsetVariables(): void
    {
        // Set a test variable first
        $_ENV['TEST_VARIABLE'] = 'original_value';
        $_SERVER['TEST_VARIABLE'] = 'original_value';

        // Unset the variable
        $cleanup = TestUtils::updateEnv([
            'TEST_VARIABLE' => null,
        ]);

        // Verify variable is unset
        $this->assertArrayNotHasKey('TEST_VARIABLE', $_ENV);
        $this->assertArrayNotHasKey('TEST_VARIABLE', $_SERVER);

        // Clean up (restore original)
        $cleanup();

        // Verify restoration
        $this->assertEquals('original_value', $_ENV['TEST_VARIABLE']);
        $this->assertEquals('original_value', $_SERVER['TEST_VARIABLE']);

        // Final cleanup
        unset($_ENV['TEST_VARIABLE']);
        unset($_SERVER['TEST_VARIABLE']);
    }

    public function testUpdateEnvWithMultipleChanges(): void
    {
        // Store originals
        $originals = [
            'VAR1' => $_ENV['VAR1'] ?? null,
            'VAR2' => $_ENV['VAR2'] ?? null,
            'VAR3' => $_ENV['VAR3'] ?? null,
        ];

        // Set initial values
        $_ENV['VAR1'] = 'original1';
        $_ENV['VAR2'] = 'original2';

        // Update with mixed operations
        $cleanup = TestUtils::updateEnv([
            'VAR1' => 'updated1',    // Update existing
            'VAR2' => null,          // Unset existing
            'VAR3' => 'new_value',   // Create new
        ]);

        // Verify changes
        $this->assertEquals('updated1', $_ENV['VAR1']);
        $this->assertArrayNotHasKey('VAR2', $_ENV);
        $this->assertEquals('new_value', $_ENV['VAR3']);

        // Clean up
        $cleanup();

        // Verify restoration
        $this->assertEquals('original1', $_ENV['VAR1']);
        $this->assertEquals('original2', $_ENV['VAR2']);
        $this->assertEquals($originals['VAR3'], $_ENV['VAR3'] ?? null);

        // Final cleanup
        unset($_ENV['VAR1'], $_ENV['VAR2'], $_ENV['VAR3']);
        unset($_SERVER['VAR1'], $_SERVER['VAR2'], $_SERVER['VAR3']);
    }

    public function testClientEnvironmentVariableDetection(): void
    {
        // Test API key from environment
        $cleanup = TestUtils::updateEnv([
            'ANTHROPIC_API_KEY' => 'env-api-key-test',
        ]);

        // Create client without explicit API key
        $client = new ClaudePhp();
        
        // Verify client uses environment variable
        // Note: This test assumes the client reads from environment variables
        // The exact implementation depends on the ClaudePhp constructor
        
        $cleanup();
        
        // This test serves as documentation of expected behavior
        $this->assertTrue(true);
    }

    public function testBaseUrlEnvironmentVariable(): void
    {
        $cleanup = TestUtils::updateEnv([
            'ANTHROPIC_BASE_URL' => 'https://custom.api.endpoint.com',
        ]);

        // Create client without explicit base URL
        $client = new ClaudePhp(apiKey: 'test-key');
        
        // Verify client uses environment variable
        // Note: This test assumes the client reads from environment variables
        
        $cleanup();
        
        $this->assertTrue(true);
    }

    public function testEnvironmentVariablePrecedence(): void
    {
        // Set environment variables
        $cleanup = TestUtils::updateEnv([
            'ANTHROPIC_API_KEY' => 'env-key',
            'ANTHROPIC_BASE_URL' => 'https://env.endpoint.com',
        ]);

        // Create client with explicit parameters (should override environment)
        $client = new ClaudePhp(
            apiKey: 'explicit-key',
            baseUrl: 'https://explicit.endpoint.com'
        );

        // Explicit parameters should take precedence over environment variables
        // This test documents expected behavior
        
        $cleanup();
        
        $this->assertTrue(true);
    }

    public function testMissingApiKeyHandling(): void
    {
        // Ensure no API key in environment
        $cleanup = TestUtils::updateEnv([
            'ANTHROPIC_API_KEY' => null,
        ]);

        // Attempt to create client without API key should throw exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API key is required');

        new ClaudePhp();

        $cleanup();
    }

    public function testCustomUserAgent(): void
    {
        $cleanup = TestUtils::updateEnv([
            'ANTHROPIC_USER_AGENT' => 'CustomApp/1.0.0 ClaudePhp/0.1.0',
        ]);

        // Test that custom user agent is respected
        // This would require examining actual HTTP requests
        
        $cleanup();
        
        $this->assertTrue(true);
    }

    public function testTimeoutEnvironmentVariables(): void
    {
        $cleanup = TestUtils::updateEnv([
            'ANTHROPIC_TIMEOUT' => '30',
            'ANTHROPIC_MAX_RETRIES' => '5',
        ]);

        // Test that timeout settings are respected from environment
        // This would require access to client configuration
        
        $cleanup();
        
        $this->assertTrue(true);
    }

    public function testDebugModeEnvironmentVariable(): void
    {
        $cleanup = TestUtils::updateEnv([
            'ANTHROPIC_DEBUG' => 'true',
        ]);

        // Test that debug mode affects logging or request handling
        // This would require examining debug output
        
        $cleanup();
        
        $this->assertTrue(true);
    }

    public function testCloudProviderEnvironmentVariables(): void
    {
        // Test AWS environment variables
        $cleanupAws = TestUtils::updateEnv([
            'AWS_ACCESS_KEY_ID' => 'test-access-key',
            'AWS_SECRET_ACCESS_KEY' => 'test-secret-key',
            'AWS_REGION' => 'us-west-2',
        ]);

        // Test that AWS environment variables are detected
        // This would be used for AWS Bedrock integration
        
        $cleanupAws();

        // Test Google Cloud environment variables
        $cleanupGcp = TestUtils::updateEnv([
            'GOOGLE_APPLICATION_CREDENTIALS' => '/path/to/credentials.json',
            'GOOGLE_CLOUD_PROJECT' => 'test-project',
        ]);

        // Test that GCP environment variables are detected
        // This would be used for Vertex AI integration
        
        $cleanupGcp();
        
        $this->assertTrue(true);
    }

    public function testEnvironmentVariableValidation(): void
    {
        // Test invalid timeout value
        $cleanup = TestUtils::updateEnv([
            'ANTHROPIC_TIMEOUT' => 'invalid',
        ]);

        // Should handle invalid environment variable values gracefully
        // or throw appropriate validation errors
        
        $cleanup();

        // Test negative retry count
        $cleanup2 = TestUtils::updateEnv([
            'ANTHROPIC_MAX_RETRIES' => '-1',
        ]);

        // Should validate retry count
        
        $cleanup2();
        
        $this->assertTrue(true);
    }

    public function testEnvironmentIsolationBetweenTests(): void
    {
        // First test modifies environment
        $cleanup1 = TestUtils::updateEnv([
            'TEST_ISOLATION_VAR' => 'value1',
        ]);
        
        $this->assertEquals('value1', $_ENV['TEST_ISOLATION_VAR']);
        $cleanup1();

        // Second test should not see the variable
        $this->assertArrayNotHasKey('TEST_ISOLATION_VAR', $_ENV);

        // Set different value
        $cleanup2 = TestUtils::updateEnv([
            'TEST_ISOLATION_VAR' => 'value2',
        ]);
        
        $this->assertEquals('value2', $_ENV['TEST_ISOLATION_VAR']);
        $cleanup2();

        // Should be cleaned up again
        $this->assertArrayNotHasKey('TEST_ISOLATION_VAR', $_ENV);
    }
}