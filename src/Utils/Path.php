<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

/**
 * Path template utilities for safe URL construction.
 *
 * Percent-encodes interpolated values and rejects path-traversal attempts.
 */
class Path
{
    /**
     * Interpolate named placeholders in a URL path template.
     *
     * Each {name} is replaced with the percent-encoded value from $values.
     * Rejects dot-segment traversals (., .., %2e, %2E variants).
     *
     * @param string $template e.g. "/v1/agents/{agent_id}/versions"
     * @param array<string, string> $values e.g. ['agent_id' => 'ag_123']
     *
     * @throws \InvalidArgumentException on traversal attempt or missing placeholder
     */
    public static function pathTemplate(string $template, array $values): string
    {
        return (string) preg_replace_callback('/\{(\w+)\}/', static function (array $matches) use ($values): string {
            $name = $matches[1];
            if (!isset($values[$name])) {
                throw new \InvalidArgumentException("Missing path parameter: {$name}");
            }

            $value = $values[$name];
            self::rejectTraversal($value, $name);

            return rawurlencode($value);
        }, $template);
    }

    /**
     * Reject dot-segment path traversals including percent-encoded variants.
     */
    private static function rejectTraversal(string $value, string $paramName): void
    {
        $decoded = rawurldecode($value);

        $segments = explode('/', $decoded);
        foreach ($segments as $segment) {
            if ('.' === $segment || '..' === $segment) {
                throw new \InvalidArgumentException(
                    "Path parameter '{$paramName}' contains a dot-segment traversal: {$value}"
                );
            }
        }
    }
}
