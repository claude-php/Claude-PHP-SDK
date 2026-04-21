<?php

require_once __DIR__ . '/../helpers.php';

$client = createClient();

echo "=== Managed Agents Sessions API ===\n\n";

try {
    // Create a session (requires an agent_id)
    $session = $client->beta()->sessions()->create([
        'agent_id' => 'agent_example_id',
    ]);
    echo "Created session: {$session['id']}\n";

    // Send an event to the session
    $client->beta()->sessions()->events()->send($session['id'], [
        'type' => 'message',
        'role' => 'user',
        'content' => 'Hello from the PHP SDK!',
    ]);

    // List events
    $events = $client->beta()->sessions()->events()->list($session['id']);
    echo "Events: " . count($events['data'] ?? []) . "\n";

    // List session resources
    $resources = $client->beta()->sessions()->resources()->list($session['id']);
    echo "Resources: " . count($resources['data'] ?? []) . "\n";

    // Delete the session
    $client->beta()->sessions()->delete($session['id']);
    echo "Session deleted.\n";
} catch (\Throwable $e) {
    echo "Sessions API: {$e->getMessage()}\n";
    echo "(Managed Agents requires beta access)\n";
}
