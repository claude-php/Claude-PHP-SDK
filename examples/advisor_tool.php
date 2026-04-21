<?php

require_once __DIR__ . '/helpers.php';

use ClaudePhp\Types\Beta\BetaAdvisorTool20260301Param;

$client = createClient();

// The advisor tool enables a nested model call within the tool-use loop.
// The primary model can consult a secondary model (the "advisor") for help.

$advisorTool = new BetaAdvisorTool20260301Param(
    model: 'claude-sonnet-4-6',
    max_uses: 3,
);

echo "Advisor tool definition:\n";
echo json_encode($advisorTool->toArray(), JSON_PRETTY_PRINT) . "\n\n";

// Use via Beta Messages API
try {
    $response = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-6',
        'max_tokens' => 500,
        'tools' => [$advisorTool->toArray()],
        'messages' => [
            ['role' => 'user', 'content' => 'What is the capital of Australia? Use the advisor if you need help.'],
        ],
    ]);

    echo "Response:\n";
    foreach ($response->content as $block) {
        echo "  [{$block['type']}] ";
        if (isset($block['text'])) {
            echo $block['text'];
        }
        echo "\n";
    }
} catch (\Throwable $e) {
    echo "Note: Advisor tool requires beta access. Error: {$e->getMessage()}\n";
}
