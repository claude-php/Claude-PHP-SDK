<?php

require_once __DIR__ . '/../helpers.php';

$client = createClient();

echo "=== Managed Agents User Profiles API ===\n\n";

try {
    $profile = $client->beta()->userProfiles()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
    echo "Created profile: {$profile['id']}\n";

    $profiles = $client->beta()->userProfiles()->list();
    echo "Profiles: " . count($profiles['data'] ?? []) . "\n";

    // Create enrollment URL
    $enrollment = $client->beta()->userProfiles()->createEnrollmentUrl($profile['id']);
    echo "Enrollment URL: {$enrollment['url']}\n";
} catch (\Throwable $e) {
    echo "User Profiles API: {$e->getMessage()}\n";
    echo "(Managed Agents requires beta access)\n";
}
