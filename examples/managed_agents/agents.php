<?php

require_once __DIR__ . '/../helpers.php';

$client = createClient();

// Managed Agents API — create, configure, and manage persistent Claude agents.
// All operations require the managed-agents beta header (handled automatically).

echo "=== Managed Agents API ===\n\n";

// List existing agents
try {
    $agents = $client->beta()->agents()->list();
    echo "Existing agents: " . count($agents['data'] ?? []) . "\n";
} catch (\Throwable $e) {
    echo "List agents: {$e->getMessage()}\n";
}

// Create an agent
try {
    $agent = $client->beta()->agents()->create([
        'name' => 'PHP SDK Test Agent',
        'description' => 'A test agent created from the PHP SDK',
        'model' => 'claude-sonnet-4-6',
        'system_prompt' => 'You are a helpful assistant created via the PHP SDK.',
    ]);
    echo "Created agent: {$agent['id']}\n";

    // Retrieve the agent
    $retrieved = $client->beta()->agents()->retrieve($agent['id']);
    echo "Retrieved: {$retrieved['name']}\n";

    // List versions
    $versions = $client->beta()->agents()->versions()->list($agent['id']);
    echo "Versions: " . count($versions['data'] ?? []) . "\n";

    // Archive the agent
    $client->beta()->agents()->archive($agent['id']);
    echo "Agent archived.\n";
} catch (\Throwable $e) {
    echo "Agents API: {$e->getMessage()}\n";
    echo "(Managed Agents requires beta access)\n";
}
