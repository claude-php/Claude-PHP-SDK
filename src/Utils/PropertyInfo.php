<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

/**
 * Metadata class for property transformation information.
 *
 * Used in Annotated types to provide information about field transformation,
 * such as field aliasing and custom formatting.
 *
 * @internal
 */
class PropertyInfo
{
    /**
     * @param null|string $alias Field name alias (e.g., for camelCase conversion)
     * @param null|string $format Format type: 'iso8601', 'base64', or 'custom'
     * @param null|string $formatTemplate Custom format template for dates
     * @param null|string $discriminator Discriminator field name for union types
     */
    public function __construct(
        public ?string $alias = null,
        public ?string $format = null,
        public ?string $formatTemplate = null,
        public ?string $discriminator = null,
    ) {
    }

    public function __repr(): string
    {
        return sprintf(
            "%s(alias='%s', format=%s, formatTemplate='%s', discriminator='%s')",
            self::class,
            $this->alias ?? '',
            $this->format ?? '',
            $this->formatTemplate ?? '',
            $this->discriminator ?? '',
        );
    }
}
