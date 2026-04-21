<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Aws;

/**
 * AWS Signature Version 4 signer.
 *
 * Mirrors Python `src/anthropic/lib/aws/_auth.py` (which delegates to botocore).
 * This is a self-contained pure-PHP SigV4 implementation, no aws-sdk-php required.
 */
class SigV4
{
    public const ALGORITHM = 'AWS4-HMAC-SHA256';

    public function __construct(
        private readonly Credentials $credentials,
        private readonly string $region,
        private readonly string $service = 'bedrock',
    ) {
    }

    /**
     * Sign an HTTP request and return the headers to attach.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $url Full URL including query string
     * @param array<string, string> $headers Existing headers (will be merged into the canonical request)
     * @param string $body Raw request body
     * @return array<string, string> Headers including Authorization, X-Amz-Date, etc.
     */
    public function signRequest(
        string $method,
        string $url,
        array $headers = [],
        string $body = '',
    ): array {
        $parsed = parse_url($url);
        if (false === $parsed) {
            throw new \InvalidArgumentException("Invalid URL: {$url}");
        }

        $host = $parsed['host'] ?? '';
        $path = $parsed['path'] ?? '/';
        $query = $parsed['query'] ?? '';

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $amzDate = $now->format('Ymd\THis\Z');
        $dateStamp = $now->format('Ymd');

        $signedHeaders = array_merge($headers, [
            'host' => $host,
            'x-amz-date' => $amzDate,
        ]);

        if (null !== $this->credentials->sessionToken) {
            $signedHeaders['x-amz-security-token'] = $this->credentials->sessionToken;
        }

        $canonicalRequest = $this->buildCanonicalRequest($method, $path, $query, $signedHeaders, $body);
        $credentialScope = "{$dateStamp}/{$this->region}/{$this->service}/aws4_request";
        $stringToSign = self::ALGORITHM . "\n" . $amzDate . "\n" . $credentialScope . "\n" . hash('sha256', $canonicalRequest);

        $signingKey = $this->deriveSigningKey($dateStamp);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $signedHeaderNames = self::signedHeaderNames($signedHeaders);
        $authorization = sprintf(
            '%s Credential=%s/%s, SignedHeaders=%s, Signature=%s',
            self::ALGORITHM,
            $this->credentials->accessKey,
            $credentialScope,
            $signedHeaderNames,
            $signature,
        );

        $result = [
            'Authorization' => $authorization,
            'X-Amz-Date' => $amzDate,
        ];
        if (null !== $this->credentials->sessionToken) {
            $result['X-Amz-Security-Token'] = $this->credentials->sessionToken;
        }

        return $result;
    }

    /**
     * @param array<string, string> $headers
     */
    private function buildCanonicalRequest(
        string $method,
        string $path,
        string $query,
        array $headers,
        string $body,
    ): string {
        $canonicalUri = self::canonicalUri($path);
        $canonicalQuery = self::canonicalQuery($query);
        $canonicalHeaders = self::canonicalHeaders($headers);
        $signedHeaderNames = self::signedHeaderNames($headers);
        $payloadHash = hash('sha256', $body);

        return strtoupper($method) . "\n"
            . $canonicalUri . "\n"
            . $canonicalQuery . "\n"
            . $canonicalHeaders . "\n"
            . $signedHeaderNames . "\n"
            . $payloadHash;
    }

    private static function canonicalUri(string $path): string
    {
        if ('' === $path) {
            return '/';
        }

        // Each segment must be percent-encoded; SigV4 double-encodes for non-S3 services.
        $segments = explode('/', $path);
        $encoded = array_map(static fn (string $s): string => rawurlencode($s), $segments);

        return implode('/', $encoded);
    }

    private static function canonicalQuery(string $query): string
    {
        if ('' === $query) {
            return '';
        }

        parse_str($query, $params);
        ksort($params);
        $parts = [];
        foreach ($params as $k => $v) {
            $parts[] = rawurlencode((string) $k) . '=' . rawurlencode((string) $v);
        }

        return implode('&', $parts);
    }

    /**
     * @param array<string, string> $headers
     */
    private static function canonicalHeaders(array $headers): string
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = trim((string) $value);
        }
        ksort($normalized);

        $lines = [];
        foreach ($normalized as $name => $value) {
            $lines[] = $name . ':' . $value;
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param array<string, string> $headers
     */
    private static function signedHeaderNames(array $headers): string
    {
        $names = array_map('strtolower', array_keys($headers));
        sort($names);

        return implode(';', $names);
    }

    private function deriveSigningKey(string $dateStamp): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $this->credentials->secretKey, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', $this->service, $kRegion, true);

        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}
