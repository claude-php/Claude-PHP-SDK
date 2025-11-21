<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Message count tokens tool parameter
 *
 * @readonly
 */
class MessageCountTokensToolParam
{
    /**
     * @param string $type The type identifier ("tool")
     * @param string $name The tool name
     * @param null|array<string, mixed> $input_schema The input schema
     */
    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly ?array $input_schema = null,
    ) {
    }
}
