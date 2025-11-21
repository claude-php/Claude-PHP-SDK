<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use ReflectionFunction;
use ReflectionMethod;

/**
 * Reflection utilities for function and method inspection.
 *
 * Provides helpers for inspecting function signatures and parameters.
 */
final class Reflection
{
    /**
     * Check if a function has a specific parameter.
     *
     * @param callable $func The function to inspect
     * @param string $argName The parameter name to check
     *
     * @return bool True if the function has the parameter
     */
    public static function functionHasArgument(callable $func, string $argName): bool
    {
        try {
            if (is_array($func)) {
                $reflection = new ReflectionMethod($func[0], $func[1]);
            } elseif (is_string($func) && str_contains($func, '::')) {
                [$class, $method] = explode('::', $func);
                $reflection = new ReflectionMethod($class, $method);
            } else {
                $reflection = new ReflectionFunction($func);
            }

            foreach ($reflection->getParameters() as $param) {
                if ($param->getName() === $argName) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get all parameter names from a callable.
     *
     * @param callable $func The function to inspect
     *
     * @return string[] Array of parameter names
     */
    public static function getParameterNames(callable $func): array
    {
        try {
            if (is_array($func)) {
                $reflection = new ReflectionMethod($func[0], $func[1]);
            } elseif (is_string($func) && str_contains($func, '::')) {
                [$class, $method] = explode('::', $func);
                $reflection = new ReflectionMethod($class, $method);
            } else {
                $reflection = new ReflectionFunction($func);
            }

            return array_map(
                static fn ($param) => $param->getName(),
                $reflection->getParameters(),
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Check if two callables have compatible signatures.
     *
     * Compares parameter names and types of two functions to ensure they
     * can be used interchangeably.
     *
     * @param callable $sourceFunc The source function to compare against
     * @param callable $checkFunc The function to check
     * @param string[] $excludeParams Parameter names to exclude from comparison
     *
     * @return bool True if signatures are compatible
     */
    public static function signaturesInSync(
        callable $sourceFunc,
        callable $checkFunc,
        array $excludeParams = [],
    ): bool {
        try {
            $sourceParams = self::getParameterNames($sourceFunc);
            $checkParams = self::getParameterNames($checkFunc);

            foreach ($sourceParams as $param) {
                if (in_array($param, $excludeParams, true)) {
                    continue;
                }

                if (!in_array($param, $checkParams, true)) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get type information for function parameters.
     *
     * @param callable $func The function to inspect
     *
     * @return array<string, null|string> Map of parameter names to type names
     */
    public static function getParameterTypes(callable $func): array
    {
        try {
            if (is_array($func)) {
                $reflection = new ReflectionMethod($func[0], $func[1]);
            } elseif (is_string($func) && str_contains($func, '::')) {
                [$class, $method] = explode('::', $func);
                $reflection = new ReflectionMethod($class, $method);
            } else {
                $reflection = new ReflectionFunction($func);
            }

            $types = [];
            foreach ($reflection->getParameters() as $param) {
                $type = $param->getType();
                $types[$param->getName()] = $type ? $type->getName() : null;
            }

            return $types;
        } catch (\Throwable) {
            return [];
        }
    }
}
