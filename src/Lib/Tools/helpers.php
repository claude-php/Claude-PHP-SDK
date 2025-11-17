<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Tools;

/**
 * Convenience helper mirroring Python's @beta_tool decorator.
 *
 * Example:
 * use function ClaudePhp\Lib\Tools\beta_tool;
 *
 * $getWeather = beta_tool(
 *     handler: function (array $args): string { return 'sunny'; },
 *     name: 'get_weather',
 *     description: 'Fetch the weather',
 *     inputSchema: [...]
 * );
 */
function beta_tool(
    callable $handler,
    string $name,
    string $description = '',
    array $inputSchema = []
): BetaToolDefinition {
    return new BetaToolDefinition(
        name: $name,
        handler: $handler,
        description: $description,
        inputSchema: $inputSchema
    );
}
