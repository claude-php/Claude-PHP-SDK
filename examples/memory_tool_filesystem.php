<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Lib\Tools\MemoryTool\LocalFilesystemMemoryTool;

// The LocalFilesystemMemoryTool stores memory as files on disk.
// Files are created with restrictive 0600 permissions and path traversal is rejected.

$baseDir = sys_get_temp_dir() . '/claude_memory_demo';
$tool = new LocalFilesystemMemoryTool($baseDir);

echo "Memory tool definition:\n";
echo json_encode($tool->toDict(), JSON_PRETTY_PRINT) . "\n\n";

// Create a memory file
$result = $tool->execute([
    'command' => 'create',
    'path' => 'project-notes.md',
    'content' => "# Project Notes\n\n- Started on 2026-04-21\n- Using Claude PHP SDK v0.7.0\n",
]);
echo "Create: " . json_encode($result) . "\n";

// View the file
$result = $tool->execute(['command' => 'view', 'path' => 'project-notes.md']);
echo "View:\n{$result['content']}\n";

// Replace content
$result = $tool->execute([
    'command' => 'str_replace',
    'path' => 'project-notes.md',
    'old_str' => 'v0.7.0',
    'new_str' => 'v0.7.0 (with managed agents)',
]);
echo "Replace: " . json_encode($result) . "\n";

// Insert a line
$result = $tool->execute([
    'command' => 'insert',
    'path' => 'project-notes.md',
    'insert_after_line' => 3,
    'new_str' => '- Added filesystem memory tool',
]);
echo "Insert: " . json_encode($result) . "\n";

// View final result
$result = $tool->execute(['command' => 'view', 'path' => 'project-notes.md']);
echo "\nFinal content:\n{$result['content']}\n";

// Cleanup
$tool->execute(['command' => 'delete', 'path' => 'project-notes.md']);
echo "\nCleaned up.\n";
