<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Sessions;

use ClaudePhp\Resources\Resource as BaseResource;
use ClaudePhp\Utils\Path;

class Resources extends BaseResource
{
    public function add(string $sessionId, array $params = []): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}/resources', ['session_id' => $sessionId]);

        return $this->_post($path, $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function retrieve(string $sessionId, string $resourceId): array
    {
        $path = Path::pathTemplate(
            '/sessions/{session_id}/resources/{resource_id}',
            ['session_id' => $sessionId, 'resource_id' => $resourceId],
        );

        return $this->_get($path, null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function update(string $sessionId, string $resourceId, array $params = []): array
    {
        $path = Path::pathTemplate(
            '/sessions/{session_id}/resources/{resource_id}',
            ['session_id' => $sessionId, 'resource_id' => $resourceId],
        );

        return $this->_post($path, $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function list(string $sessionId, array $params = []): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}/resources', ['session_id' => $sessionId]);

        return $this->_get($path, null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function delete(string $sessionId, string $resourceId): array
    {
        $path = Path::pathTemplate(
            '/sessions/{session_id}/resources/{resource_id}',
            ['session_id' => $sessionId, 'resource_id' => $resourceId],
        );

        return $this->_delete($path, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }
}
