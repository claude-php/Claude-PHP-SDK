<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Vaults;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Credentials extends Resource
{
    public function create(string $vaultId, array $params = []): array
    {
        $path = Path::pathTemplate('/vaults/{vault_id}/credentials', ['vault_id' => $vaultId]);

        return $this->_post($path, $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function retrieve(string $vaultId, string $credentialId): array
    {
        $path = Path::pathTemplate(
            '/vaults/{vault_id}/credentials/{credential_id}',
            ['vault_id' => $vaultId, 'credential_id' => $credentialId],
        );

        return $this->_get($path, null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function update(string $vaultId, string $credentialId, array $params = []): array
    {
        $path = Path::pathTemplate(
            '/vaults/{vault_id}/credentials/{credential_id}',
            ['vault_id' => $vaultId, 'credential_id' => $credentialId],
        );

        return $this->_post($path, $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function list(string $vaultId, array $params = []): array
    {
        $path = Path::pathTemplate('/vaults/{vault_id}/credentials', ['vault_id' => $vaultId]);

        return $this->_get($path, null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function delete(string $vaultId, string $credentialId): array
    {
        $path = Path::pathTemplate(
            '/vaults/{vault_id}/credentials/{credential_id}',
            ['vault_id' => $vaultId, 'credential_id' => $credentialId],
        );

        return $this->_delete($path, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function archive(string $vaultId, string $credentialId): array
    {
        $path = Path::pathTemplate(
            '/vaults/{vault_id}/credentials/{credential_id}/archive',
            ['vault_id' => $vaultId, 'credential_id' => $credentialId],
        );

        return $this->_post($path, [], ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }
}
