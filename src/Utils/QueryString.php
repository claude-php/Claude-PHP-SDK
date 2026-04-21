<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

/**
 * Query string builder supporting multiple array serialization formats.
 */
class QueryString
{
    public const FORMAT_BRACKETS = 'brackets';
    public const FORMAT_COMMA = 'comma';
    public const FORMAT_REPEAT = 'repeat';
    public const FORMAT_INDICES = 'indices';

    /**
     * Build a URL query string from an associative array.
     *
     * @param array<string, mixed> $params
     * @param string $arrayFormat One of brackets, comma, repeat, indices
     */
    public static function build(array $params, string $arrayFormat = self::FORMAT_BRACKETS): string
    {
        $parts = [];

        foreach ($params as $key => $value) {
            if (null === $value) {
                continue;
            }

            if (\is_array($value)) {
                $parts = array_merge($parts, self::serializeArray($key, $value, $arrayFormat));
            } elseif (\is_bool($value)) {
                $parts[] = rawurlencode($key) . '=' . ($value ? 'true' : 'false');
            } else {
                $parts[] = rawurlencode($key) . '=' . rawurlencode((string) $value);
            }
        }

        return implode('&', $parts);
    }

    /**
     * Merge user-supplied query params with hardcoded params, preserving hardcoded values.
     *
     * @param array<string, mixed> $hardcoded
     * @param array<string, mixed> $userParams
     * @return array<string, mixed>
     */
    public static function mergePreservingHardcoded(array $hardcoded, array $userParams): array
    {
        return array_merge($userParams, $hardcoded);
    }

    /**
     * @return list<string>
     */
    private static function serializeArray(string $key, array $values, string $format): array
    {
        $encoded = rawurlencode($key);
        $parts = [];

        switch ($format) {
            case self::FORMAT_COMMA:
                $serialized = implode(',', array_map(static fn ($v) => rawurlencode((string) $v), $values));
                $parts[] = "{$encoded}={$serialized}";
                break;

            case self::FORMAT_REPEAT:
                foreach ($values as $v) {
                    $parts[] = "{$encoded}=" . rawurlencode((string) $v);
                }
                break;

            case self::FORMAT_INDICES:
                foreach (array_values($values) as $i => $v) {
                    $parts[] = "{$encoded}[{$i}]=" . rawurlencode((string) $v);
                }
                break;

            case self::FORMAT_BRACKETS:
            default:
                foreach ($values as $v) {
                    $parts[] = "{$encoded}[]=" . rawurlencode((string) $v);
                }
                break;
        }

        return $parts;
    }
}
