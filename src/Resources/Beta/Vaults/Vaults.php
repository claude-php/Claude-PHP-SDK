<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Vaults;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Vaults extends Resource
{
    public function create(array $params = []): array
    {
        return $this->_post('/vaults', $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function retrieve(string $vaultId): array
    {
        return $this->_get(
            Path::pathTemplate('/vaults/{vault_id}', ['vault_id' => $vaultId]),
            null,
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function update(string $vaultId, array $params = []): array
    {
        return $this->_post(
            Path::pathTemplate('/vaults/{vault_id}', ['vault_id' => $vaultId]),
            $params,
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function list(array $params = []): array
    {
        return $this->_get('/vaults', null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function delete(string $vaultId): array
    {
        return $this->_delete(
            Path::pathTemplate('/vaults/{vault_id}', ['vault_id' => $vaultId]),
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function archive(string $vaultId): array
    {
        return $this->_post(
            Path::pathTemplate('/vaults/{vault_id}/archive', ['vault_id' => $vaultId]),
            [],
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function credentials(): Credentials
    {
        return new Credentials($this->client);
    }
}
