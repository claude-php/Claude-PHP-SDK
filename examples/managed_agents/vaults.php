<?php

require_once __DIR__ . '/../helpers.php';

$client = createClient();

echo "=== Managed Agents Vaults API ===\n\n";

try {
    $vault = $client->beta()->vaults()->create(['name' => 'PHP SDK Test Vault']);
    echo "Created vault: {$vault['id']}\n";

    // Add a credential
    $cred = $client->beta()->vaults()->credentials()->create($vault['id'], [
        'name' => 'test-api-key',
        'credential_type' => 'api_key',
    ]);
    echo "Created credential: {$cred['id']}\n";

    // List credentials
    $creds = $client->beta()->vaults()->credentials()->list($vault['id']);
    echo "Credentials: " . count($creds['data'] ?? []) . "\n";

    // Cleanup
    $client->beta()->vaults()->delete($vault['id']);
    echo "Vault deleted.\n";
} catch (\Throwable $e) {
    echo "Vaults API: {$e->getMessage()}\n";
    echo "(Managed Agents requires beta access)\n";
}
