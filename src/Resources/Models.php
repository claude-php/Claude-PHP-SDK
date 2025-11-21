<?php

declare(strict_types=1);

namespace ClaudePhp\Resources;

use ClaudePhp\Types\Model;

/**
 * Models resource for the Claude API.
 *
 * Provides methods for listing and retrieving available models.
 */
class Models extends Resource
{
    /**
     * List all available models.
     *
     * Returns a paginated list of all available Claude models.
     *
     * @param array<string, mixed> $params Query parameters:
     *                                     - limit: int (optional) - Number of models to return (max 100)
     *                                     - before_id: string (optional) - Pagination cursor
     *
     * @return array Response with models array and pagination
     */
    public function list(array $params = []): array
    {
        return $this->_get('/models', $params);
    }

    /**
     * Retrieve a specific model by ID.
     *
     * Gets detailed information about a specific model.
     *
     * @param string $modelId The model ID to retrieve
     *
     * @return Model Model information object
     */
    public function retrieve(string $modelId): array
    {
        if (empty($modelId)) {
            throw new \InvalidArgumentException('model_id is required');
        }

        return $this->_get("/models/{$modelId}");
    }
}
