<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta advisor tool parameter (type: advisor_20260301).
 *
 * Enables a nested model call as an advisor within the tool-use loop.
 */
class BetaAdvisorTool20260301Param
{
    public function __construct(
        public readonly string $type = 'advisor_20260301',
        public readonly string $name = 'advisor',
        public readonly ?string $model = null,
        public readonly ?bool $caching = null,
        public readonly ?array $cache_control = null,
        public readonly ?array $allowed_callers = null,
        public readonly ?bool $defer_loading = null,
        public readonly ?int $max_uses = null,
        public readonly ?bool $strict = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'name' => $this->name,
            'model' => $this->model,
            'caching' => $this->caching,
            'cache_control' => $this->cache_control,
            'allowed_callers' => $this->allowed_callers,
            'defer_loading' => $this->defer_loading,
            'max_uses' => $this->max_uses,
            'strict' => $this->strict,
        ], static fn ($v) => null !== $v);
    }
}
