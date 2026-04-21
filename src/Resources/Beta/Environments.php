<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Environments extends Resource
{
    public function create(array $params = []): array
    {
        return $this->_post('/environments', $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function retrieve(string $environmentId): array
    {
        return $this->_get(
            Path::pathTemplate('/environments/{environment_id}', ['environment_id' => $environmentId]),
            null,
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function update(string $environmentId, array $params = []): array
    {
        return $this->_post(
            Path::pathTemplate('/environments/{environment_id}', ['environment_id' => $environmentId]),
            $params,
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function list(array $params = []): array
    {
        return $this->_get('/environments', null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function delete(string $environmentId): array
    {
        return $this->_delete(
            Path::pathTemplate('/environments/{environment_id}', ['environment_id' => $environmentId]),
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function archive(string $environmentId): array
    {
        return $this->_post(
            Path::pathTemplate('/environments/{environment_id}/archive', ['environment_id' => $environmentId]),
            [],
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }
}
