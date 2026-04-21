# Changelog

All notable changes to the Claude PHP SDK will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added (Full-Parity Closeout)

- **AWS SigV4 Signing**: Pure-PHP `SigV4` signer in `src/Lib/Aws/SigV4.php` (no aws-sdk-php required)
- **AWS Credentials Resolution**: `Credentials::resolve()` from explicit args, env vars, or `~/.aws/credentials` profile
- **`AnthropicAws`**: Now exposes `authHeaders($method, $url, $extra, $body)` returning either Bearer token or fully-signed SigV4 headers
- **Bedrock Mantle**: Now uses the SDK `HttpClient` (with retries, error mapping, timeout) instead of `file_get_contents`
- **Async Sub-Resource Wrapping**: `AsyncResourceProxy::__call()` detects sub-resource accessors (zero-arg methods returning a `Resource`) and wraps them in another async proxy, enabling `$client->async()->beta()->agents()->versions()->list(...)` chains
- **Per-Endpoint Array Format**: `HttpClient::setArrayFormat()` allows switching between `brackets`, `comma`, `repeat`, `indices` (used by Mantle for `comma`)
- **Path Safety Everywhere**: `Path::pathTemplate()` now applied to `Models`, `Beta\Models`, `Beta\Skills\Skills`, `Beta\Skills\Versions`, `Beta\Batches`, `Messages\Batches` (was Files + managed-agents only)
- **Skills Beta Header**: `Beta\Skills\Skills` and `Beta\Skills\Versions` now send `anthropic-beta: skills-2025-10-02` matching Python
- **Managed Agents Beta Header Fix**: All managed-agents resources now send `anthropic-beta: managed-agents-2026-04-01` (was the wrong placeholder `managed-agents`)
- **Deprecation Warning Caching**: Models warned about once per process (cached via `self::$warnedModels`), matching Python's `DeprecationWarning` behavior
- **Async Tool Runners**: `AsyncToolRunner` and `AsyncStreamingToolRunner` now propagate `container.id` and emit deprecation warnings on `compaction_control`
- **AsyncMessageStreamManager**: Now handles `stop_details`, `container`, `thinking_delta`, `signature_delta`, `citations_delta`, `input_json_delta` and tolerates unknown delta types
- **Bedrock/Vertex Model Mappings**: Comprehensive mapping for all current Claude 3/3.5/3.7/4 model IDs to real Bedrock ARNs and Vertex `model@version` IDs; pass-through for already-formatted IDs

### Added Types

- **Managed Agents Param Classes** (16 files in `src/Types/Beta/ManagedAgents/Params/`):
  `AgentCreateParams`, `AgentUpdateParams`, `AgentListParams`, `SessionCreateParams`,
  `SessionUpdateParams`, `SessionListParams`, `VaultCreateParams`, `VaultUpdateParams`,
  `VaultListParams`, `EnvironmentCreateParams`, `EnvironmentUpdateParams`,
  `EnvironmentListParams`, `UserProfileCreateParams`, `UserProfileUpdateParams`,
  `UserProfileListParams`, `SkillCreateParams`, `FileUploadParams`
- **Managed Agents Domain Types** (20+ new files in `src/Types/Beta/ManagedAgents/`):
  `AlwaysAllowPolicy`, `AlwaysAskPolicy`, `AnthropicSkill`, `BranchCheckout`, `CommitCheckout`,
  `CacheCreationUsage`, `AgentToolConfig`, `AgentToolset20260401`, `AgentToolsetDefaultConfig`,
  `CustomToolInputSchema`, `McpToolConfig`, `McpToolsetDefaultConfig`, `McpServerUrlDefinition`,
  `Model`, `SessionResource`, `FileResource`, `GitHubRepositoryResource`, `DeletedCredential`,
  `StaticBearerAuthResponse`, `McpOAuthAuthResponse`
- **Sessions Sub-Types** (`src/Types/Beta/Sessions/`):
  `Events` (constants for all session event discriminators), `SpanModelUsage`, `SessionEndTurn`,
  `SessionErrorEvent`, `SessionRequiresAction`, `RetryStatus`
- **Vaults Sub-Types** (`src/Types/Beta/Vaults/`):
  `CredentialTypes`, `StaticBearerCreateParams`, `StaticBearerUpdateParams`,
  `McpOAuthCreateParams`, `TokenEndpointAuth`
- **Agents Sub-Types** (`src/Types/Beta/Agents/`): `VersionListParams`
- **GA + Beta Capability Types** (10 files):
  `CapabilitySupport`, `EffortCapability`, `ThinkingCapability`, `ContextManagementCapability`,
  `ModelCapabilities`, plus Beta mirrors
- **Container Types**: `Container`, `ContainerUploadBlock(+Param)`, `BetaContainer`,
  `BetaContainerParams`, `BetaContainerUploadBlock(+Param)`
- **Network Types**: `BetaLimitedNetwork(+Param)`, `BetaUnrestrictedNetwork(+Param)`
- **Compaction Types**: `BetaCompactionBlock(+Param)`, `BetaCompactionContentBlockDelta`,
  `BetaCompactionIterationUsage`, `BetaIterationsUsage`, `BetaMessageIterationUsage`
- **Thinking Turns**: `BetaThinkingTurnsParam`
- **Web Fetch v3** (Mar 2026): `WebFetchTool20260309Param`, `BetaWebFetchTool20260309Param`
- **Server Tool Caller v2** (Jan 2026): `ServerToolCaller20260120(+Param)`,
  `BetaServerToolCaller20260120(+Param)`
- **Parsed Messages**: `ParsedMessage`, `BetaParsedMessage` (typed structured output responses)
- **Text Editor Code Execution Result Blocks** (10 files for create/strReplace/view variants
  + tool result + error blocks, GA + Beta): `TextEditorCodeExecution{Create,StrReplace,View}ResultBlock(+Param)`,
  `TextEditorCodeExecutionToolResultBlock(+Param)`, `TextEditorCodeExecutionToolResultError(+Param,+Code)`,
  plus Beta mirrors

## [0.7.0] - 2026-04-21

### Added

- **Claude Managed Agents** (Beta): Full resource support mirroring Python SDK v0.92.0
  - `$client->beta()->agents()` — create, retrieve, update, list, archive agents
  - `$client->beta()->agents()->versions()` — list agent versions
  - `$client->beta()->sessions()` — create, retrieve, update, list, delete, archive sessions
  - `$client->beta()->sessions()->events()` — list, send, stream session events
  - `$client->beta()->sessions()->resources()` — add, retrieve, update, list, delete session resources
  - `$client->beta()->vaults()` — create, retrieve, update, list, delete, archive vaults
  - `$client->beta()->vaults()->credentials()` — CRUD + archive vault credentials
  - `$client->beta()->environments()` — create, retrieve, update, list, delete, archive environments
  - `$client->beta()->userProfiles()` — create, retrieve, update, list, createEnrollmentUrl
  - Types: `Agent`, `Session`, `SessionEvent`, `Vault`, `Credential`, `Environment`,
    `DeletedSession`, `DeletedVault`, `ModelConfig`, `SessionStats`, `SessionUsage`,
    `CustomTool`, `CustomSkill`, `McpToolset`, `SessionAgent`
  - Types: `BetaCloudConfig`, `BetaCloudConfigParams`, `BetaPackages`, `BetaPackagesParams`
  - Types: `BetaUserProfile`, `BetaUserProfileEnrollmentUrl`, `BetaUserProfileTrustGrant`

- **Bedrock Mantle Client** (Python SDK v0.91.0):
  - `AnthropicBedrockMantle` / `AsyncAnthropicBedrockMantle` with Bearer token auth
  - Default URL `https://bedrock-mantle.{region}.api.aws/anthropic`
  - Env vars: `ANTHROPIC_BEDROCK_MANTLE_BASE_URL`, `AWS_BEARER_TOKEN_BEDROCK`, `ANTHROPIC_AWS_API_KEY`
  - Nested `MantleBeta` with `messages()` accessor

- **Bedrock API Key Auth** (Python SDK v0.88.0):
  - `AnthropicBedrock` now accepts `?string $apiKey` for Bearer token auth
  - Default from env `AWS_BEARER_TOKEN_BEDROCK`; mutually exclusive with SigV4 credentials

- **AWS Package**: New `src/Lib/Aws/` with `AnthropicAws` / `AsyncAnthropicAws` for shared SigV4 + Bearer auth

- **Vertex US/EU Multi-Region** (Python SDK v0.89.0 + v0.94.0):
  - Region routing: `us` → `aiplatform.us.rep.googleapis.com`, `eu` → `aiplatform.eu.rep.googleapis.com`,
    `global` → `aiplatform.googleapis.com`, default → `{region}-aiplatform.googleapis.com`

- **Beta Advisor Tool** (Python SDK v0.93.0):
  - `BetaAdvisorTool20260301Param` with model, caching, allowed_callers, max_uses, strict
  - Result/error/redacted block types: `BetaAdvisorResultBlock`, `BetaAdvisorRedactedResultBlock`,
    `BetaAdvisorToolResultBlock`, `BetaAdvisorToolResultError`, `BetaAdvisorMessageIterationUsage`

- **Structured `stop_details`** (Python SDK v0.88.0):
  - `RefusalStopDetails` / `BetaRefusalStopDetails` types (type, category, explanation)
  - Added `?array $stop_details` and `?array $container` to `Message` and `Types\Message`

- **Token Budgets** (Python SDK v0.96.0):
  - `BetaTokenTaskBudgetParam`, `BetaInputTokensTriggerParam`, `BetaInputTokensClearAtLeastParam`,
    `BetaToolUsesTriggerParam`, `BetaToolUsesKeepParam`
  - `BetaOutputConfig` / `BetaOutputConfigParam` extended with `?array $task_budget`

- **Top-level `cache_control`** (Python SDK v0.83.0):
  - Added `cache_control` to `Messages::getParamTypes()` and `getCountTokensParamTypes()`
  - Added `cache_control` to `Beta\Messages::getParamTypes()` and `getCountTokensParamTypes()`

- **MCP Conversion Helpers** (Python SDK v0.84.0):
  - `Mcp::tool()`, `Mcp::content()`, `Mcp::message()`, `Mcp::resourceToContent()`, `Mcp::resourceToFile()`
  - `UnsupportedMcpValueException` for unsupported content types

- **Filesystem Memory Tool** (Python SDK v0.86.0):
  - `AbstractMemoryTool` base with execute() dispatching to view/create/strReplace/insert/delete/rename
  - `LocalFilesystemMemoryTool` with restrictive 0600 permissions and path-traversal rejection

- **GA `display` on Thinking Config** (Python SDK v0.85.0):
  - Added optional `display` ("summarized" | "omitted") to `ThinkingConfigEnabledParam`,
    `ThinkingConfigAdaptiveParam`, `BetaThinkingConfigAdaptiveParam`

- **Path Safety Utilities**: `Path::pathTemplate()` with percent-encoding and dot-segment rejection
- **Query String Builder**: `QueryString::build()` with brackets/comma/repeat/indices formats
- **APIStatusError `type` Field** (Python SDK v0.87.0): Populated from `body.error.type`

- **Model Constants**:
  - `MODEL_CLAUDE_OPUS_4_7` = `claude-opus-4-7`
  - `MODEL_CLAUDE_MYTHOS_PREVIEW` = `claude-mythos-preview`

- **Beta `user_profile_id` Parameter** (Python SDK v0.96.0): Added to Beta Messages params
- **Beta `container` Parameter**: Added to Beta Messages params for container propagation

- **New Examples**: `automatic_caching.php`, `mcp_conversion.php`, `memory_tool_filesystem.php`,
  `bedrock_mantle.php`, `advisor_tool.php`, `managed_agents/*.php` (agents, sessions, vaults,
  environments, user_profiles)

### Changed

- **`SDK_VERSION`**: Updated from `'0.6.0'` to `'0.7.0'`
- **`composer.json`**: Bumped from `0.6.0` to `0.7.0`
- **Tool Runners**: Now propagate `container.id` from responses into subsequent request params
- **Streaming**: Tolerant of unknown delta types (compaction, advisor, etc.); handles
  `stop_details` and `container` on message delta events
- **HttpClient**: Uses `QueryString::build()` instead of `http_build_query()`; preserves
  hardcoded query params when merging with user-supplied params
- **Resource Base**: `_get()` and `_delete()` now accept `$additionalHeaders` parameter
- **Files**: `upload()` sends file data via multipart field only, not duplicated in JSON body;
  path params use `Path::pathTemplate()` for safety

### Deprecated

- **`CompactionControl` / `CompactionControlParam`**: Use server-side `compact_20260112` instead.
  Tool runners emit `E_USER_DEPRECATED` when `compaction_control` is provided.
- **Models**: `claude-opus-4-0`, `claude-opus-4-20250514`, `claude-sonnet-4-0`,
  `claude-sonnet-4-20250514` emit `E_USER_DEPRECATED` on `create()` (EOL 2026-06-15).

### Python SDK Parity

This release brings the PHP SDK to parity with the Python Anthropic SDK from
v0.83.0 through v0.96.0 (Feb 19 – Apr 16, 2026).

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
