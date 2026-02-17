# Changelog

All notable changes to the Claude PHP SDK will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.6.1] - 2026-02-18

### Changed

- Added GitHub Stars badge and star request to README

## [0.6.0] - 2026-02-18

### Added

- **Adaptive Thinking Support**: New `ThinkingConfigAdaptiveParam` type (and `BetaThinkingConfigAdaptiveParam`)
  - `type: "adaptive"` — model automatically decides whether and how much to think per request
  - Recommended default mode for `claude-opus-4-6` and newer models
  - No `budget_tokens` required; model optimises cost vs. quality automatically
  - Mirrors Python SDK v0.78.0 feature

- **Speed / Fast-Mode Parameter** (Beta Messages):
  - `speed: "fast"` — high-throughput inference with lower latency
  - `speed: "standard"` — full quality responses (default)
  - Added to `Beta\Messages::getParamTypes()` and `getCountTokensParamTypes()`
  - Mirrors Python SDK v0.79.0 feature

- **`output_config` in GA Messages**:
  - Added `output_config` to `Messages::getParamTypes()` for structured outputs in the stable API
  - Mirrors Python SDK v0.77.0 migration from `output_format` to `output_config`

- **Model Constants** in `ModelParam`:
  - `MODEL_CLAUDE_OPUS_4_6` = `claude-opus-4-6` (Feb 2026)
  - `MODEL_CLAUDE_SONNET_4_6` = `claude-sonnet-4-6` (Feb 2026)
  - `MODEL_CLAUDE_3_7_SONNET_LATEST` = `claude-3-7-sonnet-latest`
  - `MODEL_CLAUDE_3_7_SONNET_20250219` = `claude-3-7-sonnet-20250219`
  - `MODEL_CLAUDE_HAIKU_4_5` / `MODEL_CLAUDE_HAIKU_4_5_ALIAS` and full model family constants
  - All current Claude 4, 3.7, 3.5, and 3 legacy model IDs as typed constants

- **Code Execution Tool Types** (entirely new, GA + Beta):
  - `CodeExecutionTool20250522Param` — type `code_execution_20250522`
  - `CodeExecutionTool20250825Param` — type `code_execution_20250825` (enhanced sandbox)
  - `CodeExecutionOutputBlock` / `CodeExecutionOutputBlockParam` — file outputs from execution
  - `CodeExecutionResultBlock` / `CodeExecutionResultBlockParam` — execution results
  - `CodeExecutionToolResultBlock` / `CodeExecutionToolResultBlockParam` — tool result wrappers
  - `CodeExecutionToolResultError` / `CodeExecutionToolResultErrorCode` — error types
  - Beta: `BetaCodeExecutionTool20260120Param` — type `code_execution_20260120` (REPL state persistence)
  - Beta result/output/error block equivalents (`BetaCodeExecution*`)

- **Memory Tool Types** (new):
  - `MemoryTool20250818Param` — GA memory tool (type `memory_20250818`)
  - `BetaMemoryTool20250818Param` — Beta memory tool
  - Memory command classes (all commands): `BetaMemoryTool20250818ViewCommand`,
    `BetaMemoryTool20250818CreateCommand`, `BetaMemoryTool20250818StrReplaceCommand`,
    `BetaMemoryTool20250818InsertCommand`, `BetaMemoryTool20250818DeleteCommand`,
    `BetaMemoryTool20250818RenameCommand`

- **Web Fetch Tool Types** (new):
  - `WebFetchTool20250910Param` — GA web fetch tool (type `web_fetch_20250910`)
  - `WebFetchBlock` / `WebFetchBlockParam` — fetched content blocks
  - `WebFetchToolResultBlock` / `WebFetchToolResultBlockParam` — tool result wrappers
  - `WebFetchToolResultErrorBlock` / `WebFetchToolResultErrorBlockParam` — error blocks
  - `WebFetchToolResultErrorCode` — error code constants
  - Beta: `BetaWebFetchTool20260209Param` — type `web_fetch_20260209` (with `allowed_callers`)
  - Beta web fetch block/result/error equivalents (`BetaWebFetch*`)

- **Beta Web Search v2**:
  - `BetaWebSearchTool20260209Param` — type `web_search_20260209` with `allowed_callers` support
  - Mirrors Python SDK v0.80.0 feature

- **New Examples**:
  - `examples/adaptive_thinking.php` — Adaptive thinking mode with claude-opus-4-6
  - `examples/fast_mode.php` — Speed parameter (fast/standard) via Beta Messages API
  - `examples/web_fetch.php` — Web fetch tool with domain restrictions and citations
  - `examples/code_execution.php` — Code execution with file outputs and REPL state persistence
  - `examples/memory_tool.php` — Memory tool with all command types

### Changed

- **`SDK_VERSION` constant**: Updated from `'0.1.0'` to `'0.6.0'` in `ClaudePhp::SDK_VERSION`
- **`composer.json` version**: Bumped from `0.5.3` to `0.6.0`
- **`ModelParam`**: Converted from a minimal wrapper class to a fully documented class with
  typed constants for all current model IDs

## [0.5.3] - 2025-01-17

### Added

- **Comprehensive Integration Tests**: Added `NewFeaturesIntegrationTest` for v0.5.2 features
  - Server-side tool handling tests
  - Authentication flexibility tests
  - Stream closure and resource cleanup tests
  - Binary streaming method verification
  - Mixed client/server tool scenarios
  - 6 new integration test cases (318 total tests passing)

- **Tutorial 16: v0.5.2 New Features**: Complete tutorial for latest SDK capabilities
  - Server-side vs client-side tools explained
  - Authentication flexibility patterns (OAuth2, Bearer tokens, proxies)
  - Enhanced stream management best practices
  - Binary request streaming examples
  - Mixed tools agent implementation
  - Runnable example code with comprehensive documentation

### Changed

- Updated `tutorials/README.md` with Tutorial 16 in learning path
- Updated `examples/README.md` with documentation for new examples
  - Added `server_side_tools.php` reference
  - Added `authentication_flexibility.php` reference
- Enhanced project documentation for v0.5.2 features

## [0.5.2] - 2025-01-17

### Added

- **Server-Side Tools Support**: Tool runners now handle server-side tool execution
  - `BetaToolRunner`, `ToolRunner`, and `StreamingToolRunner` support `server_tool_use` blocks
  - Server-side tools (e.g., code execution) are recognized but not executed locally
  - Automatic differentiation between client-side and server-side tool calls
  - Mirrors Python SDK v0.76.0 feature (#1086)

- **Binary Request Streaming**: Support for sending binary data with streaming responses
  - New `HttpClient::postStreamBinary()` method for binary request bodies
  - `Resource::_postStreamBinary()` helper for resource classes
  - Configurable Content-Type for binary payloads
  - Enables advanced use cases like binary uploads with real-time processing

- **Stream Closure Guarantees**: Enhanced resource management for streaming responses
  - `StreamResponse` now includes `__destruct()` for automatic cleanup
  - Idempotent `close()` method prevents duplicate close attempts
  - Ensures network resources are freed even without explicit close
  - Mirrors Python SDK v0.76.0 stream closure improvements

### Changed

- **Authentication Flexibility**: Loosened API key validation for alternative auth methods
  - API key is now optional if custom auth headers are provided
  - Supports custom `x-api-key`, `Authorization`, or other auth headers
  - Empty API key allowed when `customHeaders` include authentication
  - Enables Bearer tokens, service accounts, and proxy-based auth
  - Mirrors Python SDK v0.76.0 auth header validation changes

### Fixed

- PATCH method verified to correctly handle file uploads and multipart data
  - FileExtraction utility properly processes files in PATCH requests
  - Async PATCH operations handle file conversions correctly

## [0.5.1] - 2025-11-26

### Added

- **Official Laravel Package**: Released `claude-php/claude-php-sdk-laravel` v1.0.0
  - Service provider with auto-registration
  - `Claude` facade for expressive API access
  - Publishable configuration with environment variable support
  - Full dependency injection support
  - Laravel 11.x and 12.x compatible
  - Comprehensive documentation with agentic patterns (ReAct, multi-tool agents, frameworks)
  - 👉 [Laravel Package Repository](https://github.com/claude-php/Claude-PHP-SDK-Laravel)

### Changed

- Updated README with Laravel package installation and usage instructions
- Added dedicated Framework Integrations section for Laravel

## [0.5.0] - 2025-11-26

### Added

- **Computer Use V5 (20251124)**: New computer use tool with enhanced features
  - Zoom capability for detailed screen inspection
  - Allowed callers for security control
  - Deferred loading for performance optimization
  - Multi-display support with display_number
  - Strict mode for validation
  - Types: `ToolComputerUse20251124`, `BetaToolComputerUse20251124`

- **Search Tools (20251119)**: Dynamic tool discovery
  - BM25 search tool: `ToolSearchToolBM25_20251119`
  - Regex search tool: `ToolSearchToolRegex20251119`
  - Tool reference blocks for search results
  - Result and error types for search operations

- **MCP Toolset Configuration**: Model Context Protocol support
  - Types: `MCPToolset`, `MCPToolConfig`, `MCPToolDefaultConfig`
  - Per-tool configuration overrides
  - Default configurations for server tools

- **Output Config with Effort Levels**: Control response quality
  - Types: `OutputConfig`, `BetaOutputConfig`
  - Effort levels: low, medium, high
  - Trade-off between latency and response depth

- **Auto-Compaction**: Context window management
  - Types: `CompactionControl`
  - Automatic message history summarization
  - Configurable token thresholds
  - Custom summary prompts

- **Tool Caller Types**: Advanced tool invocation control
  - `DirectCaller` / `BetaDirectCaller`
  - `ServerToolCaller` / `BetaServerToolCaller`
  - `ToolReferenceBlock` / `BetaToolReferenceBlock`

- **New Examples**:
  - `auto_compaction.php` - Context window management
  - `effort_levels.php` - Controlling response quality
  - `tool_search.php` - Dynamic tool discovery
  - `mcp_toolset.php` - MCP server configuration
  - `computer_use_v5.php` - Enhanced computer use

- **New Tutorial**:
  - Tutorial 15: Context Management & Advanced Tool Use

### Changed

- Updated Beta types for new features
- Enhanced documentation with new feature coverage

### Fixed

- Fixed duplicate `/v1/` prefix in API paths for `Batches.delete()`, `Messages.countTokens()`, `Beta/Messages.countTokens()`, and `Completions.create()`
- Fixed `batch_processing.php` example to use array notation for API responses
- Fixed Tutorial 15 helpers include path

## [0.4.0] - 2025-11-25

### Added

- **Claude Opus 4.5 Support**: Updated to support the newly released Claude Opus 4.5 model
  - Model ID: `claude-opus-4-5-20251101`
  - New pricing: $5/$25 per million tokens (input/output)
  - State-of-the-art performance for coding, agents, and computer use

### Changed

- Updated all model references from Opus 4.1 to Opus 4.5
- Updated model mappings in Bedrock and Vertex integrations
- Updated pricing information in documentation and examples

## [0.3.0] - 2025-01-21

### Added

- **Microsoft Azure AI Foundry Integration**: Full support for accessing Claude through Azure AI Foundry
  - `AnthropicFoundry` class for synchronous operations
  - `AsyncAnthropicFoundry` class for asynchronous operations using Amphp
  - Dual authentication support: API key and Azure AD token provider
  - Complete feature parity with direct API (streaming, tools, vision, etc.)
  - Comprehensive example file (`examples/foundry.php`) with 8 usage scenarios
  - Documentation in `docs/foundry.md`
  - Unit tests for Foundry integration
- Updated README.md with Cloud Platform Integrations section
- Updated examples/README.md with Foundry examples listing

## [0.2.0] - 2025-11-22

### Added

- **50+ Comprehensive Examples**: Expanded from 29 to 80+ examples (11,000+ lines)
  - **Streaming Examples (8)**: Basic streaming, event handling, tool use, extended thinking, error recovery, web search, message accumulation
  - **Batch Processing Examples (8)**: Create, list, poll, cancel, retrieve results, complete workflow, with caching
  - **Citations Examples (7)**: Basic citations, multiple documents, streaming citations, large documents, context handling, disabled citations
  - **Extended Thinking Examples (6)**: Thinking with tools, interleaved thinking, redacted thinking, thinking block clearing, continuation patterns
  - **Context Management Examples (4)**: Advanced configuration, combining strategies, tool result clearing, thinking block clearing
  - **Basic Examples (4)**: Basic request, multi-turn conversations, response prefilling, vision
  - **Caching Examples (2)**: Message-level caching, system prompt caching
  - **Test Runners (4)**: Test suites for streaming, batch, and citations examples
- **Helper Functions**: Added `createClient()` helper function in `examples/helpers.php`
- **Documentation**: Updated README with comprehensive example listings and statistics

### Changed

- Updated README.md to reflect 80+ examples covering all Claude documentation pages
- Improved examples organization with clear categorization
- Enhanced examples/README.md with streaming examples section

### Fixed

- Resolved merge conflicts in examples (extended_thinking.php, memory_tool.php, prompt_caching.php, quickstart.php, token_counting.php, README.md)
- Removed trailing whitespace from example files

## [0.1.0] - 2025-11-17

### Added

- Initial SDK release with core infrastructure
- Exception hierarchy with proper error handling
- PSR-12 compliant code structure
- PSR-18 HTTP client support
- Base resource classes for API endpoints
- Response objects for Messages API
- Comprehensive documentation and examples
- PHPUnit test infrastructure
- PHPStan static analysis integration
- Full project scaffolding

### Features

- **Foundation**: Core `Anthropic` client class for configuration and initialization
- **Exception Handling**: Complete exception hierarchy mirroring Python SDK
  - `APIStatusError` for 4xx/5xx responses
  - `RateLimitError` for 429 rate limiting
  - `AuthenticationError` for 401 auth failures
  - `APIConnectionError` for network issues
  - `APITimeoutError` for timeout scenarios
  - And more specific error types
- **Response Objects**: 
  - `Message` for message responses
  - `TextContent`, `ToolUseContent`, `ToolResultContent` for content blocks
  - `Usage` for token tracking
- **Code Quality**:
  - PSR-12 coding standards compliance
  - Type-safe properties and methods
  - PHPStan level 9 analysis support

### Project Structure

- `src/Exceptions/` - Exception hierarchy
- `src/Resources/` - Base resource class for API endpoints
- `src/Responses/` - Response data objects
- `src/Contracts/` - Interfaces for dependency injection
- `src/Client/` - HTTP client implementation (in progress)
- `src/Requests/` - Request builders (in progress)
- `tests/` - Unit tests (in progress)

### To Do (Next Releases)

- Complete HTTP client implementation
- Message API resource with streaming
- Files API implementation
- Batch processing API
- Models API
- Embeddings API
- Comprehensive test suite
- Async/await support via Amphp

---

See [milestones](https://github.com/claude-php/claude-php-sdk/milestones) for planned releases.
