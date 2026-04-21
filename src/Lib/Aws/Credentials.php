<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Aws;

/**
 * AWS credential resolution helpers.
 *
 * Mirrors Python `src/anthropic/lib/aws/_credentials.py`.
 * Resolution order: explicit args -> env vars -> profile -> error.
 */
class Credentials
{
    public function __construct(
        public readonly string $accessKey,
        public readonly string $secretKey,
        public readonly ?string $sessionToken = null,
    ) {
    }

    /**
     * Resolve credentials from explicit args, environment, or AWS profile.
     */
    public static function resolve(
        ?string $accessKey = null,
        ?string $secretKey = null,
        ?string $sessionToken = null,
        ?string $profile = null,
    ): self {
        if (null !== $accessKey && null !== $secretKey) {
            return new self($accessKey, $secretKey, $sessionToken);
        }

        $envAccess = $_ENV['AWS_ACCESS_KEY_ID'] ?? getenv('AWS_ACCESS_KEY_ID') ?: null;
        $envSecret = $_ENV['AWS_SECRET_ACCESS_KEY'] ?? getenv('AWS_SECRET_ACCESS_KEY') ?: null;
        $envSession = $_ENV['AWS_SESSION_TOKEN'] ?? getenv('AWS_SESSION_TOKEN') ?: null;

        if (null !== $envAccess && null !== $envSecret) {
            return new self($envAccess, $envSecret, $envSession ?: $sessionToken);
        }

        if (null !== $profile) {
            $resolved = self::loadProfile($profile);
            if (null !== $resolved) {
                return $resolved;
            }
        }

        throw new \RuntimeException(
            'Unable to resolve AWS credentials. Provide accessKey/secretKey, set AWS_ACCESS_KEY_ID/AWS_SECRET_ACCESS_KEY environment variables, or supply a valid profile name.'
        );
    }

    /**
     * Load credentials from ~/.aws/credentials profile.
     */
    private static function loadProfile(string $profile): ?self
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: null;
        if (null === $home) {
            return null;
        }

        $path = rtrim($home, '/') . '/.aws/credentials';
        if (!is_file($path)) {
            return null;
        }

        $parsed = parse_ini_file($path, true, INI_SCANNER_RAW);
        if (false === $parsed || !isset($parsed[$profile])) {
            return null;
        }

        $section = $parsed[$profile];
        if (!isset($section['aws_access_key_id'], $section['aws_secret_access_key'])) {
            return null;
        }

        return new self(
            accessKey: $section['aws_access_key_id'],
            secretKey: $section['aws_secret_access_key'],
            sessionToken: $section['aws_session_token'] ?? null,
        );
    }
}
