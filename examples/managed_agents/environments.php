<?php

require_once __DIR__ . '/../helpers.php';

$client = createClient();

echo "=== Managed Agents Environments API ===\n\n";

try {
    $env = $client->beta()->environments()->create([
        'name' => 'PHP SDK Test Environment',
        'variables' => ['DEBUG' => 'true'],
    ]);
    echo "Created environment: {$env['id']}\n";

    $envList = $client->beta()->environments()->list();
    echo "Environments: " . count($envList['data'] ?? []) . "\n";

    $client->beta()->environments()->delete($env['id']);
    echo "Environment deleted.\n";
} catch (\Throwable $e) {
    echo "Environments API: {$e->getMessage()}\n";
    echo "(Managed Agents requires beta access)\n";
}
