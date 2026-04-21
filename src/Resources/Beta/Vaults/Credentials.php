<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Vaults;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Credentials extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'managed-agents-2026-04-01'];

    public function create(string $vaultId, array $params = []): array
    {
        $path = Path::pathTemplate('/vaults/{vault_id}/credentials?beta=true', ['vault_id' => $vaultId]);

        return $this->_post($path, $params, self::BETA_HEADER);
    }

    public function retrieve(string $vaultId, string $credentialId): array
    {
        $path = Path::pathTemplate(
            '/vaults/{vault_id}/credentials/{credential_id}?beta=true',
            ['vault_id' => $vaultId, 'credential_id' => $credentialId],
        );

        return $this->_get($path, null, self::BETA_HEADER);
    }

    public function update(string $vaultId, string $credentialId, array $params = []): array
    {
        $path = Path::pathTemplate(
            '/vaults/{vault_id}/credentials/{credential_id}?beta=true',
            ['vault_id' => $vaultId, 'credential_id' => $credentialId],
        );

        return $this->_post($path, $params, self::BETA_HEADER);
    }

    public function list(string $vaultId, array $params = []): array
    {
        $path = Path::pathTemplate('/vaults/{vault_id}/credentials?beta=true', ['vault_id' => $vaultId]);

        return $this->_get($path, $params, self::BETA_HEADER);
    }

    public function delete(string $vaultId, string $credentialId): array
    {
        $path = Path::pathTemplate(
            '/vaults/{vault_id}/credentials/{credential_id}?beta=true',
            ['vault_id' => $vaultId, 'credential_id' => $credentialId],
        );

        return $this->_delete($path, self::BETA_HEADER);
    }

    public function archive(string $vaultId, string $credentialId): array
    {
        $path = Path::pathTemplate(
            '/vaults/{vault_id}/credentials/{credential_id}/archive?beta=true',
            ['vault_id' => $vaultId, 'credential_id' => $credentialId],
        );

        return $this->_post($path, [], self::BETA_HEADER);
    }
}
