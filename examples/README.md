# Claude PHP SDK Examples

This directory contains example scripts demonstrating various features of the Claude PHP SDK.

## Setup

1. Create a `.env` file in the project root with your API key:
```
ANTHROPIC_API_KEY=your-api-key-here
```

2. Run any example:
```bash
php examples/messages.php
```

## Examples

### Getting Started

- **quickstart.php** - The simplest possible example to get started (matches the docs homepage example)
- **get_started.php** - Complete getting started guide with examples from the Claude documentation, including:
  - Simple web search assistant
  - Basic hello world
  - Multiple conversational turns
  - System prompts
  - Response prefilling
  - Temperature control
- **working_with_messages.php** - Practical patterns for the Messages API (from the [Working with Messages](https://docs.claude.com/en/docs/build-with-claude/working-with-messages) guide):
  - Basic request and response
  - Multiple conversational turns (stateless API)
  - Prefilling responses (putting words in Claude's mouth)
  - Vision with base64-encoded images
  - Vision with URL-referenced images
  - Multiple images in one request
- **context_windows.php** - Context window management (from the [Context Windows](https://docs.claude.com/en/docs/build-with-claude/context-windows) guide):
  - Understanding token usage and limits
  - Multi-turn token accumulation
  - Token estimation techniques
  - Extended thinking token management
  - Context awareness in Claude 4.5 models
  - 1M token context window (beta)
  - Managing context window limits
- **prompt_caching.php** - Reduce costs and latency (from the [Prompt Caching](https://docs.claude.com/en/docs/build-with-claude/prompt-caching) guide):
  - Basic prompt caching with system messages
  - Caching large documents
  - Caching tool definitions
  - Multi-turn conversations with caching
  - Best practices and optimization tips
  - 90% cost reduction for cached content
- **context_editing.php** - Automatic context management (from the [Context Editing](https://docs.claude.com/en/docs/build-with-claude/context-editing) guide) - **BETA**:
  - Basic tool result clearing
  - Advanced configuration (trigger, keep, clear_at_least, exclude_tools)
  - Thinking block clearing strategies
  - Combining multiple strategies
  - Configuration options reference
  - Using with Memory Tool
  - Token counting with context management

### Core Capabilities

- **streaming_comprehensive.php** - Complete streaming guide (from the [Streaming Messages](https://docs.claude.com/en/docs/build-with-claude/streaming) guide):
  - Basic streaming with SSE
  - All event types (message_start, content_block_delta, etc.)
  - Streaming with tool use
  - Streaming with extended thinking
  - Streaming with web search
  - Error handling in streams
- **batch_processing.php** - 50% cost savings with batches (from the [Batch Processing](https://docs.claude.com/en/docs/build-with-claude/batch-processing) guide):
  - Creating message batches
  - Listing batches
  - Retrieving results
  - Canceling batches
  - Status tracking
- **citations.php** - Source attribution (from the [Citations](https://docs.claude.com/en/docs/build-with-claude/citations) guide) - **BETA**:
  - Basic citations with documents
  - Citations response structure
  - Multiple documents with document_index
  - Use cases for compliance and RAG
- **token_counting.php** - Cost planning (from the [Token Counting](https://docs.claude.com/en/docs/build-with-claude/token-counting) guide):
  - Basic token counting
  - Counting with system prompts
  - Counting with tools
  - Manual estimation techniques
- **embeddings.php** - Semantic search (from the [Embeddings](https://docs.claude.com/en/docs/build-with-claude/embeddings) guide):
  - Embedding generation concepts
  - Available Voyage AI models
  - Use cases (semantic search, RAG, clustering)
- **structured_outputs.php** - Guaranteed JSON (from the [Structured Outputs](https://docs.claude.com/en/docs/build-with-claude/structured-outputs) guide):
  - Basic structured output with parse()
  - Complex nested schemas
  - Streaming structured outputs
  - Schema design tips
  
### Basic Usage

- **messages.php** - Basic message creation and multi-turn conversations
- **messages_stream.php** - Basic streaming example
- **error_handling.php** - Comprehensive error handling patterns
- **model_comparison.php** - Compare different Claude models

### Streaming Examples

See [STREAMING_EXAMPLES.md](STREAMING_EXAMPLES.md) for comprehensive streaming documentation based on the [official Claude streaming docs](https://docs.claude.com/en/docs/build-with-claude/streaming).

- **streaming_basic.php** - Simple text streaming
- **streaming_with_events.php** - Event-driven streaming with all event types
- **streaming_with_tools.php** - Streaming with tool use (function calling)
- **streaming_extended_thinking.php** - Streaming with extended thinking
- **streaming_message_accumulation.php** - Building complete messages from stream
- **streaming_error_recovery.php** - Error handling and retry logic
- **streaming_web_search.php** - Streaming with web search capability
- **test_streaming_examples.php** - Test runner for all streaming examples

### Vision & Documents

- **vision_comprehensive.php** - Complete vision guide (from the [Vision](https://docs.claude.com/en/docs/build-with-claude/vision) guide):
  - Base64-encoded images
  - URL-referenced images
  - Multiple images in one request
  - Best practices and optimization
- **images.php** - Basic image example with base64 encoding
- **pdf_support.php** - PDF document analysis (from the [PDF Support](https://docs.claude.com/en/docs/build-with-claude/pdf-support) guide):
  - PDF via base64
  - Limitations and considerations
  - Alternative text extraction approaches
  - Use cases (forms, invoices, contracts)
- **files_api.php** - File management (from the [Files API](https://docs.claude.com/en/docs/build-with-claude/files) guide) - **BETA**:
  - Uploading files
  - Using files in messages
  - Listing and retrieving files
  - Downloading file content
  - Deleting files
- **search_results.php** - Provide search results (from the [Search Results](https://docs.claude.com/en/docs/build-with-claude/search-results) guide):
  - Formatting search results for Claude
  - Comparison with web_search tool
  - Best practices for result formatting

### Extended Thinking

- **extended_thinking.php** - Comprehensive extended thinking guide (from the [Extended Thinking](https://docs.claude.com/en/docs/build-with-claude/extended-thinking) guide):
  - Basic extended thinking with budget configuration
  - Summarized thinking (Claude 4 models)
  - Working with thinking budgets (1K-32K+ tokens)
  - Multi-turn conversations with thinking blocks
  - Thinking redaction and safety
  - Extended thinking with tool use (interleaved thinking)
  - Token management and pricing
  - Best practices and supported models
  - Configuration examples for different use cases
- **thinking.php** - Basic extended thinking example
- **thinking_stream.php** - Stream extended thinking responses

### Tool Use (Function Calling)

- **tool_use_overview.php** - Complete tool use guide (from the [Tool Use Overview](https://docs.claude.com/en/docs/agents-and-tools/tool-use/overview)):
  - Single tool example (client-side)
  - Server-side tools (web search)
  - Complete client tool workflow
  - JSON mode with forced tool use
  - Tool choice options
  - Pricing breakdown
- **tool_use_implementation.php** - Implementation patterns (from the [Implement Tool Use](https://docs.claude.com/en/docs/agents-and-tools/tool-use/implement-tool-use) guide):
  - Multiple tools
  - Multi-turn tool conversations
  - Error handling in tool execution
  - Writing good tool descriptions
- **token_efficient_tool_use.php** - Optimize token usage (from the [Token-Efficient Tool Use](https://docs.claude.com/en/docs/agents-and-tools/tool-use/token-efficient-tool-use) guide):
  - Minimize tool descriptions
  - Leverage parameter descriptions
  - Limit number of tools
  - Optimize tool results
  - Caching strategies
- **fine_grained_tool_streaming.php** - Real-time parameters (from the [Fine-grained Tool Streaming](https://docs.claude.com/en/docs/agents-and-tools/tool-use/fine-grained-tool-streaming) guide):
  - Streaming tool parameters
  - Input JSON delta events
  - Early execution opportunity
  - Handling partial JSON
- **bash_tool.php** - Command execution (from the [Bash Tool](https://docs.claude.com/en/docs/agents-and-tools/tool-use/bash-tool) guide):
  - Bash tool setup (bash_tool_20250124)
  - Safe execution patterns
  - Security considerations
  - Whitelisting and sandboxing
- **code_execution_tool.php** - Python code execution (from the [Code Execution Tool](https://docs.claude.com/en/docs/agents-and-tools/tool-use/code-execution-tool) guide):
  - Code execution setup (code_execution_20250514)
  - Sandboxed Python execution
  - Use cases (data analysis, visualization)
  - Security best practices
- **computer_use_tool.php** - Desktop automation (from the [Computer Use Tool](https://docs.claude.com/en/docs/agents-and-tools/tool-use/computer-use-tool) guide):
  - Computer use setup (computer_use_20251022)
  - Mouse, keyboard, screenshot actions
  - Implementation patterns
  - Security warnings (experimental)
- **text_editor_tool.php** - File editing (from the [Text Editor Tool](https://docs.claude.com/en/docs/agents-and-tools/tool-use/text-editor-tool) guide):
  - Text editor setup (text_editor_20250728)
  - File operations (view, str_replace, create, insert, undo)
  - Implementation pattern
  - Use cases
- **web_fetch_tool.php** - Fetch web content (from the [Web Fetch Tool](https://docs.claude.com/en/docs/agents-and-tools/tool-use/web-fetch-tool) guide):
  - Web fetch setup (web_fetch_20250305)
  - Multiple URL fetching
  - Comparison with web_search
  - Use cases
- **memory_tool.php** - Persistent knowledge (from the [Memory Tool](https://docs.claude.com/en/docs/agents-and-tools/tool-use/memory-tool) guide):
  - Memory tool setup (memory_20250818)
  - File operations (create, read, update, delete, list)
  - Using with context editing
  - Organization best practices
- **tools.php** - Basic tool use example
- **web_search.php** - Basic web search example

### Advanced Tool Use & Context Management

- **tool_search.php** - Dynamic tool discovery:
  - BM25 search tool (tool_search_tool_bm25_20251119)
  - Regex search tool (tool_search_tool_regex_20251119)
  - Deferred tool loading for large collections
  - Tool reference blocks
- **computer_use_v5.php** - Enhanced desktop automation (Computer Use V5):
  - Computer use setup (computer_20251124)
  - Zoom capability for detailed inspection
  - Allowed callers for security
  - Multi-display support
  - Strict mode for validation
- **mcp_toolset.php** - MCP server configuration:
  - MCP toolset configuration
  - Per-tool overrides
  - Default configurations
  - Security best practices
- **auto_compaction.php** - Context window management:
  - Automatic context summarization
  - Configurable token thresholds
  - Custom summary prompts
  - Detecting compaction events
- **effort_levels.php** - Control response quality:
  - Effort levels (low, medium, high)
  - Task complexity detection
  - Performance trade-offs
  - Combining with extended thinking

### Beta Features

- **beta_features.php** - Using beta features with the anthropic-beta header

### Cloud Platform Integrations

- **foundry.php** - Microsoft Azure AI Foundry integration (from the [Claude in Microsoft Foundry](https://docs.claude.com/en/docs/build-with-claude/claude-in-microsoft-foundry) guide):
  - API key authentication
  - Azure AD token authentication
  - Streaming with Foundry
  - Tool use with Foundry
  - Token counting
  - Multi-turn conversations
  - Vision support
  - Error handling
  - Full feature parity with direct API

### Testing

- **test_connection.php** - Basic connectivity test with debugging output

## Common Patterns

### Loading Environment Variables

All examples use the helper functions in `helpers.php`:

```php
require_once __DIR__ . '/helpers.php';

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());
```

### Handling Responses

```php
$response = $client->messages()->create([...]);

foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'];
    }
}
```

### Streaming

```php
use ClaudePhp\Lib\Streaming\MessageStream;
use ClaudePhp\Responses\Helpers\MessageContentHelper;

$rawStream = $client->messages()->stream([...]);
$stream = new MessageStream($rawStream);

foreach ($stream as $event) {
    if ($event['type'] === 'content_block_delta') {
        echo $event['delta']['text'] ?? '';
    }
}

$final = $stream->getFinalMessage();
echo MessageContentHelper::text($final);
```

### Tool Use

```php
// 1. Send request with tools
$message = $client->messages()->create([
    'messages' => [['role' => 'user', 'content' => 'What is the weather?']],
    'tools' => [['name' => 'get_weather', ...]],
]);

// 2. Extract tool use
foreach ($message->content as $block) {
    if ($block['type'] === 'tool_use') {
        $toolUse = $block;
        break;
    }
}

// 3. Execute tool and return result
$result = execute_tool($toolUse['name'], $toolUse['input']);

$response = $client->messages()->create([
    'messages' => [
        $originalMessage,
        ['role' => 'assistant', 'content' => $message->content],
        ['role' => 'user', 'content' => [
            ['type' => 'tool_result', 'tool_use_id' => $toolUse['id'], 'content' => $result]
        ]]
    ],
    'tools' => $tools,
]);
```

## Model Selection

The examples use `claude-sonnet-4-5-20250929` which is the latest Sonnet model. You can also use:

- `claude-sonnet-4-5` - Latest Sonnet (auto-updating alias)
- `claude-haiku-4-5-20251001` - Fast, cost-effective model
- `claude-opus-4-5-20251101` - Most capable model for complex tasks

## Notes

- Extended thinking is only available on Sonnet 4.5, Opus 4, and Opus 4.5
- Web search requires the `web_search_20250305` tool type
- All streaming examples use synchronous streaming - PHP doesn't have native async/await
