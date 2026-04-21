<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Vaults;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Vaults extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'managed-agents-2026-04-01'];

    public function create(array $params = []): array
    {
        return $this->_post('/vaults?beta=true', $params, self::BETA_HEADER);
    }

    public function retrieve(string $vaultId): array
    {
        return $this->_get(
            Path::pathTemplate('/vaults/{vault_id}?beta=true', ['vault_id' => $vaultId]),
            null,
            self::BETA_HEADER,
        );
    }

    public function update(string $vaultId, array $params = []): array
    {
        return $this->_post(
            Path::pathTemplate('/vaults/{vault_id}?beta=true', ['vault_id' => $vaultId]),
            $params,
            self::BETA_HEADER,
        );
    }

    public function list(array $params = []): array
    {
        return $this->_get('/vaults?beta=true', $params, self::BETA_HEADER);
    }

    public function delete(string $vaultId): array
    {
        return $this->_delete(
            Path::pathTemplate('/vaults/{vault_id}?beta=true', ['vault_id' => $vaultId]),
            self::BETA_HEADER,
        );
    }

    public function archive(string $vaultId): array
    {
        return $this->_post(
            Path::pathTemplate('/vaults/{vault_id}/archive?beta=true', ['vault_id' => $vaultId]),
            [],
            self::BETA_HEADER,
        );
    }

    public function credentials(): Credentials
    {
        return new Credentials($this->client);
    }
}
