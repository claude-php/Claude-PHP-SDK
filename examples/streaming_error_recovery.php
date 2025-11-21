#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// Error recovery example - demonstrates how to resume streaming after an interruption
echo "=== Streaming with Error Recovery ===\n\n";

$client = createClient();

/**
 * Simulates a streaming request that might be interrupted
 */
function streamWithRecovery($client, $messages, $maxRetries = 3)
{
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        try {
            echo "Attempt " . ($attempt + 1) . "...\n";
            
            $rawStream = $client->messages()->stream([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => $messages,
            ]);

            $stream = new \ClaudePhp\Lib\Streaming\MessageStream($rawStream);
            
            foreach ($stream as $event) {
                $type = $event['type'] ?? 'unknown';
                
                switch ($type) {
                    case 'content_block_delta':
                        $delta = $event['delta'] ?? [];
                        if (($delta['type'] ?? '') === 'text_delta') {
                            echo $delta['text'] ?? '';
                            flush();
                        }
                        break;
                        
                    case 'message_stop':
                        echo "\n[Stream completed successfully]\n";
                        return true;
                }
            }
            
            return true;
            
        } catch (\ClaudePhp\Exceptions\APIConnectionError $e) {
            // Network error - can retry
            echo "\n[Connection Error] {$e->getMessage()}\n";
            $attempt++;
            
            if ($attempt < $maxRetries) {
                echo "Retrying in 2 seconds...\n\n";
                sleep(2);
            } else {
                echo "Max retries reached. Giving up.\n";
                throw $e;
            }
            
        } catch (\ClaudePhp\Exceptions\RateLimitError $e) {
            // Rate limit - should back off and retry
            echo "\n[Rate Limit Error] {$e->getMessage()}\n";
            $attempt++;
            
            if ($attempt < $maxRetries) {
                // Exponential backoff
                $waitTime = pow(2, $attempt);
                echo "Backing off for {$waitTime} seconds...\n\n";
                sleep($waitTime);
            } else {
                echo "Max retries reached. Giving up.\n";
                throw $e;
            }
            
        } catch (\ClaudePhp\Exceptions\APITimeoutError $e) {
            // Timeout - can retry
            echo "\n[Timeout Error] {$e->getMessage()}\n";
            $attempt++;
            
            if ($attempt < $maxRetries) {
                echo "Retrying...\n\n";
                sleep(1);
            } else {
                echo "Max retries reached. Giving up.\n";
                throw $e;
            }
            
        } catch (\Exception $e) {
            // Other errors - don't retry
            echo "\n[Error] {$e->getMessage()}\n";
            throw $e;
        }
    }
    
    return false;
}

try {
    $messages = [
        [
            'role' => 'user',
            'content' => 'Write a haiku about resilience.',
        ],
    ];
    
    $success = streamWithRecovery($client, $messages);
    
    if ($success) {
        echo "\n✓ Streaming with error recovery completed successfully\n";
    }
    
} catch (\Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
    exit(1);
}
