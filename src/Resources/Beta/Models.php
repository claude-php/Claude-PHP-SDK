<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Resource;

/**
 * Models resource for beta API.
 *
 * Lists and manages beta models.
 */
class Models extends Resource
{
    /**
     * List beta models.
     *
     * @param array<string, mixed> $params Query parameters
     *
     * @return array List of models
     */
    public function list(array $params = []): array
    {
        return $this->_get('/models', $params);
    }

    /**
     * Retrieve a specific model.
     *
     * @param string $modelId Model ID
     *
     * @return array Model details
     */
    public function retrieve(string $modelId): array
    {
        if (empty($modelId)) {
            throw new \InvalidArgumentException('model_id is required');
        }

        return $this->_get("/models/{$modelId}");
    }
}
