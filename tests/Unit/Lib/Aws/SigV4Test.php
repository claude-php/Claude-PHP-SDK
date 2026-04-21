<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Aws;

use ClaudePhp\Lib\Aws\Credentials;
use ClaudePhp\Lib\Aws\SigV4;
use PHPUnit\Framework\TestCase;

class SigV4Test extends TestCase
{
    public function testSignsRequestWithExpectedHeaders(): void
    {
        $creds = new Credentials('AKIAEXAMPLE', 'secret', null);
        $signer = new SigV4($creds, 'us-east-1', 'bedrock');

        $headers = $signer->signRequest('POST', 'https://bedrock-runtime.us-east-1.amazonaws.com/model/foo/invoke', [
            'content-type' => 'application/json',
        ], '{}');

        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertArrayHasKey('X-Amz-Date', $headers);
        $this->assertStringContainsString('AWS4-HMAC-SHA256', $headers['Authorization']);
        $this->assertStringContainsString('Credential=AKIAEXAMPLE/', $headers['Authorization']);
        $this->assertStringContainsString('us-east-1/bedrock/aws4_request', $headers['Authorization']);
        $this->assertMatchesRegularExpression('/^\d{8}T\d{6}Z$/', $headers['X-Amz-Date']);
    }

    public function testIncludesSecurityTokenWhenProvided(): void
    {
        $creds = new Credentials('AK', 'sec', 'session-token-value');
        $signer = new SigV4($creds, 'us-west-2', 'bedrock');

        $headers = $signer->signRequest('POST', 'https://example.com/foo', [], '');

        $this->assertSame('session-token-value', $headers['X-Amz-Security-Token']);
        $this->assertStringContainsString('x-amz-security-token', $headers['Authorization']);
    }

    public function testCredentialsResolveFromExplicitArgs(): void
    {
        $creds = Credentials::resolve(accessKey: 'a', secretKey: 'b', sessionToken: 'c');
        $this->assertSame('a', $creds->accessKey);
        $this->assertSame('b', $creds->secretKey);
        $this->assertSame('c', $creds->sessionToken);
    }
}
