<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * GA web fetch tool (type: web_fetch_20260309).
 *
 * Mirrors Python `web_fetch_tool_20260309_param.py`.
 */
class WebFetchTool20260309Param
{
    public function __construct(
        public readonly string $type = 'web_fetch_20260309',
        public readonly string $name = 'web_fetch',
        public readonly ?array $allowed_domains = null,
        public readonly ?array $blocked_domains = null,
        public readonly ?int $max_uses = null,
        public readonly ?array $cache_control = null,
        public readonly ?bool $citations = null,
        public readonly ?int $max_content_tokens = null,
        public readonly ?array $allowed_callers = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'name' => $this->name,
            'allowed_domains' => $this->allowed_domains,
            'blocked_domains' => $this->blocked_domains,
            'max_uses' => $this->max_uses,
            'cache_control' => $this->cache_control,
            'citations' => $this->citations,
            'max_content_tokens' => $this->max_content_tokens,
            'allowed_callers' => $this->allowed_callers,
        ], static fn ($v) => null !== $v);
    }
}
