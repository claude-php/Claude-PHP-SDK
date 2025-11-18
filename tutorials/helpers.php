<?php
/**
 * Shared Helper Functions for Agentic AI Tutorials
 * 
 * This file provides common utilities used across all tutorials to avoid code duplication
 * and provide consistent patterns for building AI agents.
 */

/**
 * Run an agent loop with configurable iteration limits
 * 
 * This is the core ReAct loop implementation that powers autonomous agents.
 * It continues calling Claude and executing tools until:
 * - Claude provides a final answer (stop_reason = 'end_turn')
 * - Maximum iterations reached
 * - An error occurs
 * 
 * @param ClaudePhp\ClaudePhp $client The Claude client instance
 * @param array $messages Current conversation history
 * @param array $tools Available tools for the agent
 * @param array $config Configuration options:
 *   - max_iterations: Maximum loop iterations (default: 10)
 *   - model: Model to use (default: claude-sonnet-4-5)
 *   - max_tokens: Max tokens per response (default: 4096)
 *   - debug: Show debug output (default: false)
 *   - system: System prompt (optional)
 *   - thinking: Enable extended thinking (optional)
 * 
 * @return array Final response and updated messages
 */
function runAgentLoop($client, array $messages, array $tools, array $config = []): array
{
    $maxIterations = $config['max_iterations'] ?? 10;
    $model = $config['model'] ?? 'claude-sonnet-4-5';
    $maxTokens = $config['max_tokens'] ?? 4096;
    $debug = $config['debug'] ?? false;
    $system = $config['system'] ?? null;
    $thinking = $config['thinking'] ?? null;
    
    $iteration = 0;
    $finalResponse = null;
    
    while ($iteration < $maxIterations) {
        $iteration++;
        
        if ($debug) {
            echo "\n" . str_repeat("=", 80) . "\n";
            echo "🔄 Agent Iteration {$iteration}/{$maxIterations}\n";
            echo str_repeat("=", 80) . "\n";
        }
        
        // Build request parameters
        $params = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
            'tools' => $tools,
        ];
        
        if ($system) {
            $params['system'] = $system;
        }
        
        if ($thinking) {
            $params['thinking'] = $thinking;
        }
        
        // Call Claude
        try {
            $response = $client->messages()->create($params);
        } catch (Exception $e) {
            if ($debug) {
                echo "❌ Error: {$e->getMessage()}\n";
            }
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'messages' => $messages,
                'iterations' => $iteration
            ];
        }
        
        if ($debug) {
            debugAgentStep($response, $iteration);
        }
        
        // Add assistant response to conversation
        $messages[] = [
            'role' => 'assistant',
            'content' => $response->content
        ];
        
        // Check if we're done
        if ($response->stop_reason === 'end_turn') {
            $finalResponse = $response;
            if ($debug) {
                echo "\n✅ Agent completed successfully!\n";
            }
            break;
        }
        
        // Extract and execute tool calls
        if ($response->stop_reason === 'tool_use') {
            $toolUses = extractToolUses($response);
            
            if (empty($toolUses)) {
                if ($debug) {
                    echo "⚠️  stop_reason='tool_use' but no tool uses found\n";
                }
                break;
            }
            
            // Execute each tool and collect results
            $toolResults = [];
            foreach ($toolUses as $toolUse) {
                if ($debug) {
                    echo "\n🔧 Executing tool: {$toolUse['name']}\n";
                    echo "   Input: " . json_encode($toolUse['input']) . "\n";
                }
                
                // This is where you'd execute the actual tool
                // In tutorials, we provide the tool executor function
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $toolUse['id'],
                    'content' => '[Tool executor not configured in helper]'
                ];
            }
            
            // Add tool results to conversation
            $messages[] = [
                'role' => 'user',
                'content' => $toolResults
            ];
        } else {
            // Unexpected stop reason
            if ($debug) {
                echo "⚠️  Unexpected stop_reason: {$response->stop_reason}\n";
            }
            $finalResponse = $response;
            break;
        }
    }
    
    if ($iteration >= $maxIterations && !$finalResponse) {
        if ($debug) {
            echo "\n⚠️  Max iterations reached without completion\n";
        }
    }
    
    return [
        'success' => $finalResponse !== null,
        'response' => $finalResponse,
        'messages' => $messages,
        'iterations' => $iteration
    ];
}

/**
 * Extract tool use blocks from a Claude response
 * 
 * @param mixed $response Claude API response
 * @return array Array of tool use blocks
 */
function extractToolUses($response): array
{
    $toolUses = [];
    
    foreach ($response->content as $block) {
        if (is_array($block) && ($block['type'] ?? null) === 'tool_use') {
            $toolUses[] = $block;
        } elseif (is_object($block) && isset($block->type) && $block->type === 'tool_use') {
            // Handle object format
            $toolUses[] = [
                'type' => 'tool_use',
                'id' => $block->id,
                'name' => $block->name,
                'input' => (array)$block->input
            ];
        }
    }
    
    return $toolUses;
}

/**
 * Format a tool execution result for returning to Claude
 * 
 * @param string $toolUseId The tool_use_id from Claude's request
 * @param mixed $result The result from executing the tool
 * @param bool $isError Whether this is an error result
 * @return array Formatted tool_result block
 */
function formatToolResult(string $toolUseId, $result, bool $isError = false): array
{
    $toolResult = [
        'type' => 'tool_result',
        'tool_use_id' => $toolUseId,
        'content' => is_string($result) ? $result : json_encode($result)
    ];
    
    if ($isError) {
        $toolResult['is_error'] = true;
    }
    
    return $toolResult;
}

/**
 * Debug output for agent reasoning steps
 * 
 * Shows what Claude is thinking and planning to do
 * 
 * @param mixed $response Claude API response
 * @param int $iteration Current iteration number
 */
function debugAgentStep($response, int $iteration): void
{
    echo "\n📊 Step {$iteration} Analysis:\n";
    echo str_repeat("-", 80) . "\n";
    
    // Show thinking if present
    foreach ($response->content as $block) {
        if (is_array($block)) {
            if (($block['type'] ?? null) === 'thinking') {
                $thinking = $block['thinking'] ?? '';
                echo "💭 Thinking: " . substr($thinking, 0, 200);
                if (strlen($thinking) > 200) {
                    echo "... (truncated)\n";
                } else {
                    echo "\n";
                }
            } elseif (($block['type'] ?? null) === 'text') {
                echo "💬 Response: {$block['text']}\n";
            } elseif (($block['type'] ?? null) === 'tool_use') {
                echo "🔧 Tool Call: {$block['name']}\n";
                echo "   Parameters: " . json_encode($block['input']) . "\n";
            }
        }
    }
    
    echo "🛑 Stop Reason: {$response->stop_reason}\n";
    echo "📈 Tokens: Input={$response->usage->input_tokens}, Output={$response->usage->output_tokens}\n";
}

/**
 * Manage conversation history to stay within token limits
 * 
 * This function keeps the most recent messages and removes older ones
 * to prevent hitting context window limits.
 * 
 * @param array $messages Current conversation history
 * @param int $maxMessages Maximum number of message pairs to keep
 * @return array Trimmed message history
 */
function manageConversationHistory(array $messages, int $maxMessages = 10): array
{
    if (count($messages) <= $maxMessages) {
        return $messages;
    }
    
    // Keep the most recent messages
    // Always keep user-assistant pairs
    $keep = [];
    $pairs = [];
    
    for ($i = 0; $i < count($messages); $i++) {
        $role = $messages[$i]['role'];
        if ($role === 'user') {
            // Start a new pair
            $pairs[] = [$messages[$i]];
        } elseif ($role === 'assistant' && !empty($pairs)) {
            // Complete the current pair
            $pairs[count($pairs) - 1][] = $messages[$i];
        }
    }
    
    // Keep the last N pairs
    $keepPairs = array_slice($pairs, -$maxMessages);
    
    // Flatten back to messages
    foreach ($keepPairs as $pair) {
        foreach ($pair as $msg) {
            $keep[] = $msg;
        }
    }
    
    return $keep;
}

/**
 * Calculate approximate token count for messages
 * 
 * This is a rough estimation: ~4 characters = 1 token
 * For accurate counts, use the Claude API's countTokens endpoint
 * 
 * @param array $messages Message array
 * @return int Approximate token count
 */
function estimateTokens(array $messages): int
{
    $text = json_encode($messages);
    return (int)ceil(strlen($text) / 4);
}

/**
 * Pretty print a message array for debugging
 * 
 * @param array $messages Messages to print
 * @param int $maxContentLength Maximum length of content to show
 */
function printMessages(array $messages, int $maxContentLength = 100): void
{
    echo "\n📝 Conversation History:\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($messages as $i => $message) {
        $role = $message['role'];
        $content = $message['content'];
        
        echo "\n[{$i}] {$role}:\n";
        
        if (is_string($content)) {
            $display = strlen($content) > $maxContentLength 
                ? substr($content, 0, $maxContentLength) . '...' 
                : $content;
            echo "  {$display}\n";
        } elseif (is_array($content)) {
            foreach ($content as $block) {
                if (is_array($block)) {
                    $type = $block['type'] ?? 'unknown';
                    if ($type === 'text') {
                        $text = $block['text'] ?? '';
                        $display = strlen($text) > $maxContentLength 
                            ? substr($text, 0, $maxContentLength) . '...' 
                            : $text;
                        echo "  [text] {$display}\n";
                    } elseif ($type === 'tool_use') {
                        echo "  [tool_use] {$block['name']}\n";
                    } elseif ($type === 'tool_result') {
                        $result = $block['content'] ?? '';
                        $display = strlen($result) > $maxContentLength 
                            ? substr($result, 0, $maxContentLength) . '...' 
                            : $result;
                        echo "  [tool_result] {$display}\n";
                    }
                }
            }
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
}

/**
 * Extract text content from a Claude response
 * 
 * @param mixed $response Claude API response
 * @return string Combined text from all text blocks
 */
function extractTextContent($response): string
{
    $text = [];
    
    foreach ($response->content as $block) {
        if (is_array($block) && ($block['type'] ?? null) === 'text') {
            $text[] = $block['text'];
        } elseif (is_object($block) && isset($block->type) && $block->type === 'text') {
            $text[] = $block->text;
        }
    }
    
    return implode("\n", $text);
}

/**
 * Create a simple tool definition
 * 
 * @param string $name Tool name
 * @param string $description What the tool does
 * @param array $parameters Parameter definitions
 * @param array $required Required parameter names
 * @return array Tool definition for Claude
 */
function createTool(string $name, string $description, array $parameters, array $required = []): array
{
    return [
        'name' => $name,
        'description' => $description,
        'input_schema' => [
            'type' => 'object',
            'properties' => $parameters,
            'required' => $required
        ]
    ];
}

/**
 * Simple retry wrapper with exponential backoff
 * 
 * @param callable $fn Function to retry
 * @param int $maxAttempts Maximum retry attempts
 * @param int $initialDelayMs Initial delay in milliseconds
 * @return mixed Result from function
 * @throws Exception if all retries fail
 */
function retryWithBackoff(callable $fn, int $maxAttempts = 3, int $initialDelayMs = 1000)
{
    $attempt = 0;
    $delay = $initialDelayMs;
    
    while ($attempt < $maxAttempts) {
        try {
            return $fn();
        } catch (Exception $e) {
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                throw $e;
            }
            
            // Exponential backoff
            usleep($delay * 1000);
            $delay *= 2;
        }
    }
}

/**
 * Colorize console output (for terminals that support it)
 * 
 * @param string $text Text to colorize
 * @param string $color Color name (red, green, yellow, blue, magenta, cyan)
 * @return string Colorized text
 */
function colorize(string $text, string $color): string
{
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'reset' => "\033[0m"
    ];
    
    $colorCode = $colors[$color] ?? '';
    $resetCode = $colors['reset'];
    
    return $colorCode . $text . $resetCode;
}

