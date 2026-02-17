# Tutorial 17: v0.6.0 New Features

**Time: 60 minutes** | **Difficulty: Intermediate** | **Requires: API key**

This tutorial covers all the new features introduced in **v0.6.0**, which brings the PHP SDK to full parity with the Python SDK v0.80.0. You'll learn how to use the latest Claude models, leverage adaptive reasoning, boost throughput with fast mode, and work with the new server-side tool suite.

## What You'll Learn

- **Adaptive Thinking** — Let Claude decide whether and how much to think per request
- **Speed / Fast-Mode** — High-throughput inference via `speed: "fast"` (Beta)
- **`output_config`** — Pass structured output configuration to both GA and Beta Messages
- **Model Constants** — Typed constants for all current Claude models via `ModelParam`
- **Code Execution Tool** — GA and Beta variants; REPL state persistence in Beta
- **Memory Tool** — File-based persistence across conversations (view/create/edit/delete)
- **Web Fetch Tool** — Retrieve URL content with domain restrictions and token caps
- **Beta Web Search v2** — Updated search with `allowed_callers` for multi-agent workflows

---

## Prerequisites

Ensure you have:

1. PHP 8.1+ and Composer installed
2. `claude-php/claude-php-sdk` `^0.6.0` installed
3. An `.env` file with `ANTHROPIC_API_KEY`
4. Access to `claude-opus-4-6` or `claude-sonnet-4-5-20250929` in your account

---

## Part 1: Adaptive Thinking

Adaptive thinking is a new thinking mode introduced alongside `claude-opus-4-6`. Unlike `enabled` (which always spends a fixed token budget on thinking), `adaptive` lets the model decide whether thinking is warranted for each prompt.

### When to Use Adaptive vs. Enabled

| Mode | Use case |
|------|----------|
| `disabled` | Simple tasks, fastest response, lowest cost |
| `enabled`  | Guaranteed deep reasoning; set an explicit `budget_tokens` |
| `adaptive` | Variable workloads; Claude allocates only what's needed |

### Usage

```php
use ClaudePhp\Types\ModelParam;

$response = $client->messages()->create([
    'model'      => ModelParam::MODEL_CLAUDE_OPUS_4_6,
    'max_tokens' => 4096,
    'thinking'   => ['type' => 'adaptive'],
    'messages'   => [
        ['role' => 'user', 'content' => 'Solve this complex logic puzzle: ...'],
    ],
]);

foreach ($response->content as $block) {
    if ($block['type'] === 'thinking') {
        // Model decided to think; content is the reasoning trace
        echo "Thinking: " . $block['thinking'] . "\n";
    } elseif ($block['type'] === 'text') {
        echo "Answer: " . $block['text'] . "\n";
    }
}
```

### Type Classes

```php
use ClaudePhp\Types\ThinkingConfigAdaptiveParam;
use ClaudePhp\Types\Beta\BetaThinkingConfigAdaptiveParam;

$config = new ThinkingConfigAdaptiveParam();          // GA
$betaConfig = new BetaThinkingConfigAdaptiveParam();  // Beta
// Both have type = 'adaptive'
```

---

## Part 2: Speed / Fast-mode Parameter

The `speed` parameter is available on **Beta Messages** and controls the inference mode:

- `standard` — Full quality (default)
- `fast` — Reduced latency, optimized for high-throughput scenarios

```php
$response = $client->beta()->messages()->create([
    'model'      => ModelParam::MODEL_CLAUDE_OPUS_4_6,
    'max_tokens' => 128,
    'speed'      => 'fast',
    'messages'   => [
        ['role' => 'user', 'content' => 'Classify this email as spam or not spam.'],
    ],
]);
```

`speed` also works with `countTokens`:

```php
$tokens = $client->beta()->messages()->countTokens([
    'model'    => ModelParam::MODEL_CLAUDE_OPUS_4_6,
    'speed'    => 'fast',
    'messages' => [...],
]);
```

> **Note:** `speed: "fast"` may produce shorter or less detailed responses. Best for classification, triage, and simple generation tasks where latency matters.

---

## Part 3: Code Execution Tool

Three versions of the code execution tool are available:

| Type | Channel | Key difference |
|------|---------|---------------|
| `code_execution_20250522` | GA | Initial version; `allowed_callers`, `defer_loading`, `strict` |
| `code_execution_20250825` | GA | Enhanced sandbox |
| `code_execution_20260120` | Beta | **REPL state persistence** across calls |

### Basic Usage (GA)

```php
$response = $client->messages()->create([
    'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'max_tokens' => 2048,
    'tools'      => [
        [
            'name' => 'code_execution',
            'type' => 'code_execution_20250825',
        ],
    ],
    'messages'   => [
        ['role' => 'user', 'content' => 'Generate a list of Fibonacci numbers up to 100.'],
    ],
]);
```

### REPL State (Beta)

```php
$response = $client->beta()->messages()->create([
    'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'max_tokens' => 2048,
    'tools'      => [
        [
            'name'   => 'code_execution',
            'type'   => 'code_execution_20260120',
            'strict' => true,
        ],
    ],
    'messages'   => [...],
]);
```

With `code_execution_20260120`, variables defined in one tool call are available in subsequent calls within the same session.

### Result Types

```php
// Successful result
// $block['type'] === 'code_execution_result'
// $block['stdout'] — standard output
// $block['stderr'] — standard error
// $block['return_code'] — 0 = success
// $block['content'] — array of CodeExecutionOutputBlock (file_id for generated files)

// Error result
// $block['type'] === 'code_execution_tool_result_error'
// $block['error_code'] — 'timeout' | 'execution_error' | 'internal_error'
```

---

## Part 4: Memory Tool

The memory tool (`memory_20250818`) provides a file-based storage system for cross-conversation persistence.

### Tool Registration

```php
$response = $client->messages()->create([
    'model'   => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'tools'   => [
        [
            'name' => 'memory',
            'type' => 'memory_20250818',
        ],
    ],
    'messages' => [
        ['role' => 'user', 'content' => 'Store my language preference as English.'],
    ],
]);
```

### Supported Commands

| Command | Required fields | Description |
|---------|----------------|-------------|
| `view` | `path` | Read a file or list directory |
| `create` | `path`, `file_text` | Create a new file |
| `str_replace` | `path`, `old_str`, `new_str` | Find and replace text |
| `insert` | `path`, `insert_line`, `insert_text` | Insert text at a line |
| `delete` | `path` | Delete a file or folder |
| `rename` | `old_path`, `new_path` | Rename or move a file |

### Beta Variant

Use `BetaMemoryTool20250818Param` for multi-agent workflows where you need `allowed_callers`:

```php
use ClaudePhp\Types\Beta\BetaMemoryTool20250818Param;

$tool = new BetaMemoryTool20250818Param(
    name:            'memory',
    type:            'memory_20250818',
    allowed_callers: ['direct', 'tool_use'],
);
```

---

## Part 5: Web Fetch Tool

Retrieve content from URLs for inclusion in context.

### GA Version

```php
$response = $client->messages()->create([
    'model'   => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'tools'   => [
        [
            'name'               => 'web_fetch',
            'type'               => 'web_fetch_20250910',
            'allowed_domains'    => ['docs.anthropic.com', 'example.com'],
            'max_uses'           => 3,
            'max_content_tokens' => 50000,
        ],
    ],
    'messages' => [
        ['role' => 'user', 'content' => 'Summarize https://docs.anthropic.com/'],
    ],
]);
```

### Beta Version (with `allowed_callers`)

```php
$response = $client->beta()->messages()->create([
    'tools' => [
        [
            'name'            => 'web_fetch',
            'type'            => 'web_fetch_20260209',
            'allowed_callers' => ['direct', 'tool_use'],
            'max_uses'        => 5,
        ],
    ],
    ...
]);
```

### Result Structure

```php
foreach ($response->content as $block) {
    if ($block['type'] === 'tool_use' && $block['name'] === 'web_fetch') {
        echo "Fetching: " . $block['input']['url'] . "\n";
    } elseif ($block['type'] === 'web_fetch_result') {
        // $block['url']          — URL that was fetched
        // $block['retrieved_at'] — ISO 8601 timestamp
        // $block['content']      — document content
    } elseif ($block['type'] === 'web_fetch_tool_result_error') {
        // $block['error_code'] — see WebFetchToolResultErrorCode constants
    }
}
```

### Error Codes (`WebFetchToolResultErrorCode`)

| Constant | Value |
|----------|-------|
| `INVALID_TOOL_INPUT` | `invalid_tool_input` |
| `URL_TOO_LONG` | `url_too_long` |
| `URL_NOT_ALLOWED` | `url_not_allowed` |
| `URL_NOT_ACCESSIBLE` | `url_not_accessible` |
| `MAX_USES_EXCEEDED` | `max_uses_exceeded` |
| `UNAVAILABLE` | `unavailable` |

---

## Part 6: Model Constants

`ModelParam` now provides typed constants for all current models:

```php
use ClaudePhp\Types\ModelParam;

// Claude 4.6 (Feb 2026)
ModelParam::MODEL_CLAUDE_OPUS_4_6    // 'claude-opus-4-6'
ModelParam::MODEL_CLAUDE_SONNET_4_6  // 'claude-sonnet-4-6'

// Claude 4.5 (Nov 2025)
ModelParam::MODEL_CLAUDE_OPUS_4_5    // 'claude-opus-4-5-20251101'
ModelParam::MODEL_CLAUDE_SONNET_4_5  // 'claude-sonnet-4-5-20250929'
ModelParam::MODEL_CLAUDE_HAIKU_4_5   // 'claude-haiku-4-5-20251001'

// Claude 3.7 (Feb 2025)
ModelParam::MODEL_CLAUDE_3_7_SONNET_LATEST   // 'claude-3-7-sonnet-latest'
ModelParam::MODEL_CLAUDE_3_7_SONNET_20250219 // 'claude-3-7-sonnet-20250219'

// Claude 3.5 (2024)
ModelParam::MODEL_CLAUDE_3_5_HAIKU_LATEST   // 'claude-3-5-haiku-latest'
ModelParam::MODEL_CLAUDE_3_5_HAIKU_20241022 // 'claude-3-5-haiku-20241022'

// Claude 3 (legacy)
ModelParam::MODEL_CLAUDE_3_OPUS_LATEST   // 'claude-3-opus-latest'
ModelParam::MODEL_CLAUDE_3_HAIKU_20240307 // 'claude-3-haiku-20240307'
```

> **Tip:** Using constants means your IDE can autocomplete model names and you catch typos at parse time, not runtime.

---

## Running the Tutorial

```bash
php tutorials/17-v060-features/v060_features.php
```

> Real API calls will be made and tokens will be consumed. All demos are short
> to minimize costs (< 100 output tokens each by default).

---

## What's Next

- **Tutorial 4** — Production-ready agents (memory + error handling)
- **Tutorial 5** — Advanced ReAct with extended thinking
- **examples/adaptive_thinking.php** — Adaptive vs. enabled thinking comparison
- **examples/fast_mode.php** — Speed mode performance benchmarks
- **examples/code_execution.php** — Multi-step code execution workflows
- **examples/memory_tool.php** — Memory-augmented agent sessions
- **examples/web_fetch.php** — Web fetch with citations

---

## Related Resources

- [Anthropic Models Overview](https://docs.anthropic.com/en/docs/about-claude/models/overview)
- [Extended Thinking](https://docs.anthropic.com/en/docs/build-with-claude/extended-thinking)
- [Code Execution Tool](https://docs.anthropic.com/en/docs/agents-and-tools/tool-use/code-execution-tool)
- [Memory Tool](https://docs.anthropic.com/en/docs/agents-and-tools/tool-use/memory-tool)
- [Web Fetch Tool](https://docs.anthropic.com/en/docs/agents-and-tools/tool-use/web-fetch-tool)
- [CHANGELOG.md](../../CHANGELOG.md) — Full v0.6.0 changes
