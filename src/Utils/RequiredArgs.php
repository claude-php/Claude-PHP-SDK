<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use Closure;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Validation utilities for required function arguments.
 *
 * Provides decorators and validators for enforcing that functions receive
 * specific combinations of arguments, useful for @overload patterns.
 */
final class RequiredArgs
{
    /**
     * Create a validator for required argument combinations.
     *
     * @param callable $func The function to validate
     * @param string[] $requiredArgs Names of arguments that must be provided
     * @param null|string[] $onlyIfPresent Args that are only required if specific args are present
     *
     * @return callable The wrapped function with argument validation
     */
    public static function validate(
        callable $func,
        array $requiredArgs,
        ?array $onlyIfPresent = null,
    ): callable {
        return function (...$args) use ($func, $requiredArgs, $onlyIfPresent) {
            self::checkRequiredArgs($func, $requiredArgs, $onlyIfPresent, $args);

            return $func(...$args);
        };
    }

    /**
     * Check that required arguments are provided to a function call.
     *
     * @param callable $func The function being called
     * @param string[] $requiredArgs Names of required arguments
     * @param null|string[] $onlyIfPresent Conditional requirements
     * @param mixed[] $args The actual arguments passed
     *
     * @throws \InvalidArgumentException If required arguments are missing
     */
    public static function checkRequiredArgs(
        callable $func,
        array $requiredArgs,
        ?array $onlyIfPresent = null,
        array $args = [],
    ): void {
        // Get function parameter names
        try {
            if (is_array($func)) {
                $reflection = new ReflectionMethod($func[0], $func[1]);
            } else {
                $reflection = new ReflectionFunction($func);
            }
        } catch (\Throwable) {
            // Can't introspect, skip validation
            return;
        }

        $params = $reflection->getParameters();
        $paramNames = array_map(fn ($p) => $p->getName(), $params);

        // Map positional arguments to parameter names
        $providedArgs = [];
        foreach ($args as $i => $arg) {
            if (isset($paramNames[$i])) {
                $providedArgs[$paramNames[$i]] = $arg;
            }
        }

        // Check required arguments
        $missing = [];
        foreach ($requiredArgs as $argName) {
            if (!isset($providedArgs[$argName]) || SpecialTypeUtils::isNotGiven($providedArgs[$argName])) {
                $missing[] = $argName;
            }
        }

        if ($missing) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Missing required arguments: %s. When calling %s, you must provide one of: %s',
                    implode(', ', $missing),
                    self::getFunctionName($func),
                    implode(', ', $requiredArgs),
                ),
            );
        }

        // Check conditional requirements
        if ($onlyIfPresent) {
            foreach ($onlyIfPresent as $conditionalArg => $dependentArgs) {
                if (isset($providedArgs[$conditionalArg]) && SpecialTypeUtils::isGiven($providedArgs[$conditionalArg])) {
                    foreach ((array) $dependentArgs as $dependent) {
                        if (!isset($providedArgs[$dependent]) || SpecialTypeUtils::isNotGiven($providedArgs[$dependent])) {
                            throw new \InvalidArgumentException(
                                sprintf(
                                    "When '%s' is provided to %s, you must also provide '%s'",
                                    $conditionalArg,
                                    self::getFunctionName($func),
                                    $dependent,
                                ),
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Get a human-readable function name.
     */
    private static function getFunctionName(callable $func): string
    {
        if (is_string($func)) {
            return $func;
        }

        if (is_array($func)) {
            return sprintf('%s::%s', $func[0], $func[1]);
        }

        if ($func instanceof Closure) {
            return 'Closure';
        }

        return get_class($func) . '::__invoke';
    }
}
