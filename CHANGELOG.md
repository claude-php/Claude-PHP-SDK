# Changelog

All notable changes to the Claude PHP SDK will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
