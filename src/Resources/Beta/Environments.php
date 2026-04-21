<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Environments extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'managed-agents-2026-04-01'];

    public function create(array $params = []): array
    {
        return $this->_post('/environments?beta=true', $params, self::BETA_HEADER);
    }

    public function retrieve(string $environmentId): array
    {
        return $this->_get(
            Path::pathTemplate('/environments/{environment_id}?beta=true', ['environment_id' => $environmentId]),
            null,
            self::BETA_HEADER,
        );
    }

    public function update(string $environmentId, array $params = []): array
    {
        return $this->_post(
            Path::pathTemplate('/environments/{environment_id}?beta=true', ['environment_id' => $environmentId]),
            $params,
            self::BETA_HEADER,
        );
    }

    public function list(array $params = []): array
    {
        return $this->_get('/environments?beta=true', $params, self::BETA_HEADER);
    }

    public function delete(string $environmentId): array
    {
        return $this->_delete(
            Path::pathTemplate('/environments/{environment_id}?beta=true', ['environment_id' => $environmentId]),
            self::BETA_HEADER,
        );
    }

    public function archive(string $environmentId): array
    {
        return $this->_post(
            Path::pathTemplate('/environments/{environment_id}/archive?beta=true', ['environment_id' => $environmentId]),
            [],
            self::BETA_HEADER,
        );
    }
}
