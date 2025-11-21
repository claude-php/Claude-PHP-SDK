# Changelog

All notable changes to the Claude PHP SDK will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
