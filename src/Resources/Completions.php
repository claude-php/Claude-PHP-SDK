<?php

declare(strict_types=1);

namespace ClaudePhp\Resources;

use ClaudePhp\Responses\StreamResponse;
use ClaudePhp\Utils\Transform;

/**
 * Completions resource for the Claude API (Legacy).
 *
 * The Completions API is deprecated. Use Messages API instead.
 * This resource is maintained for backward compatibility only.
 *
 * @deprecated Use Messages API instead
 */
class Completions extends Resource
{
    /**
     * Create a completion.
     *
     * @param array<string, mixed> $params Completion parameters
     * @return array Completion response
     *
     * @deprecated Use Messages API instead
     */
    public function create(array $params = []): array
    {
        $deprecated_models = [
            'claude-1',
            'claude-1-100k',
            'claude-2',
            'claude-2-100k',
            'claude-instant-1',
            'claude-instant-1-100k',
        ];

        if (isset($params['model']) && in_array($params['model'], $deprecated_models)) {
            trigger_error(
                'The Completions API and its models are deprecated. Please use the Messages API instead.',
                E_USER_DEPRECATED
            );
        }

        $body = Transform::transform($params, [
            'model' => ['type' => 'string'],
            'prompt' => ['type' => 'string'],
            'max_tokens_to_sample' => ['type' => 'int'],
            'temperature' => ['type' => 'float'],
            'top_k' => ['type' => 'int'],
            'top_p' => ['type' => 'float'],
            'stop_sequences' => ['type' => 'array'],
            'stream' => ['type' => 'bool'],
        ]);

        if (!empty($params['stream'])) {
            return $this->_postStream('/v1/completions', $body);
        }

        return $this->_post('/v1/completions', $body);
    }

    /**
     * Stream a completion.
     *
     * @param array<string, mixed> $params Completion parameters
     * @return StreamResponse Stream response
     *
     * @deprecated Use Messages API instead
     */
    public function stream(array $params = []): StreamResponse
    {
        $params['stream'] = true;
        return $this->create($params);
    }
}
