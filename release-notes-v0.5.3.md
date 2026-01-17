# Release v0.5.3 - Integration Tests & Tutorial 16

## 🎯 Overview

This patch release adds comprehensive integration tests and Tutorial 16, providing complete documentation and testing for all v0.5.2 features (Python SDK v0.76.0 parity).

## ✨ What's New

### 📊 Integration Tests

Added `NewFeaturesIntegrationTest` with 6 comprehensive test cases:

- **Server-Side Tool Handling**: Verifies `server_tool_use` blocks are properly recognized and skipped
- **Authentication Flexibility**: Tests custom headers, OAuth2, Bearer tokens
- **Stream Resource Cleanup**: Validates automatic stream closure via `__destruct()`
- **Binary Streaming**: Confirms `postStreamBinary()` method availability
- **Mixed Tool Types**: Tests client-side and server-side tools working together
- **Error Messages**: Validates helpful authentication error messages

**Test Results:**
```
✅ 318 tests passing
✅ 890 assertions
✅ 1 test skipped (requires custom auth)
```

### 🎓 Tutorial 16: v0.5.2 New Features

A comprehensive 60-minute tutorial covering all new v0.5.2 capabilities:

**Topics Covered:**

1. **Server-Side Tools**
   - Understanding `server_tool_use` vs `tool_use` blocks
   - No handler function required for server tools
   - Automatic handling in tool runners
   - Code execution and bash examples

2. **Authentication Flexibility**
   - OAuth2 Bearer token authentication
   - Custom `x-api-key` headers for proxies
   - Azure AD / Enterprise SSO integration
   - API gateway patterns
   - Dynamic token refresh
   - Environment-based configuration

3. **Enhanced Stream Management**
   - Automatic cleanup via `__destruct()`
   - Idempotent `close()` method
   - Best practices for resource management
   - Try-finally patterns

4. **Binary Request Streaming**
   - `postStreamBinary()` method usage
   - Custom Content-Type support
   - Advanced binary upload scenarios

**Files:**
- 📖 `tutorials/16-v052-features/README.md` - Complete guide (400+ lines)
- 💻 `tutorials/16-v052-features/v052_features.php` - Runnable example

### 📚 Documentation Updates

- **tutorials/README.md**: Added Tutorial 16 to the learning path
- **examples/README.md**: Enhanced with new example references:
  - `server_side_tools.php` - Server-side tool execution guide
  - `authentication_flexibility.php` - Flexible auth patterns

## 🔧 What Changed

### Added Files

```
tests/Integration/NewFeaturesIntegrationTest.php
tutorials/16-v052-features/README.md
tutorials/16-v052-features/v052_features.php
```

### Modified Files

```
composer.json (version bump)
CHANGELOG.md (v0.5.3 entry)
tutorials/README.md (Tutorial 16 added)
examples/README.md (new examples documented)
```

## 📦 Installation

### Update via Composer

```bash
composer update claude-php/claude-php-sdk
```

### Or require specific version

```bash
composer require claude-php/claude-php-sdk:^0.5.3
```

## 🚀 Usage Examples

### Running Tutorial 16

```bash
php tutorials/16-v052-features/v052_features.php
```

### Running Integration Tests

```bash
composer test -- tests/Integration/NewFeaturesIntegrationTest.php
```

### Exploring New Features

```bash
# Server-side tools
php examples/server_side_tools.php

# Authentication flexibility
php examples/authentication_flexibility.php
```

## 📖 Learning Path

**New to the SDK?** Follow the tutorial series:
1. Start with [Tutorial 0](tutorials/00-introduction/) - Introduction to Agentic AI
2. Progress through Tutorials 1-15 for foundational knowledge
3. Complete with [Tutorial 16](tutorials/16-v052-features/) for latest features

**Experienced users?** Jump directly to:
- [Tutorial 16](tutorials/16-v052-features/) - Learn v0.5.2 features
- [Server-Side Tools Example](examples/server_side_tools.php)
- [Authentication Patterns](examples/authentication_flexibility.php)

## 🔍 Key Features from v0.5.2

This release documents these previously released features:

✅ **Server-Side Tools** - Tools executed by Claude's API, not your code  
✅ **Authentication Flexibility** - OAuth2, Bearer tokens, custom headers  
✅ **Stream Cleanup** - Automatic resource management  
✅ **Binary Streaming** - Advanced binary data handling  

## 🧪 Testing

All tests pass with comprehensive coverage:

```bash
# Run all tests
composer test

# Run only integration tests
composer test -- tests/Integration/

# Run unit tests
composer test -- tests/Unit/
```

## 📊 Project Statistics

- **Total Tests:** 318 (100% passing)
- **Total Assertions:** 890
- **Tutorials:** 16 progressive tutorials
- **Examples:** 82+ comprehensive examples
- **Test Coverage:** Unit, Integration, and Feature tests

## 🐛 Bug Fixes

No bug fixes in this release - focused on documentation and testing.

## ⚠️ Breaking Changes

None - this is a fully backward-compatible patch release.

## 🙏 Acknowledgments

This release achieves complete feature parity with the Python SDK v0.76.0, ensuring PHP developers have access to all the latest Claude API capabilities.

## 📝 Full Changelog

See [CHANGELOG.md](CHANGELOG.md) for complete version history.

## 🔗 Resources

- **Documentation:** [README.md](README.md)
- **Tutorial Series:** [tutorials/README.md](tutorials/README.md)
- **Examples:** [examples/README.md](examples/README.md)
- **Python SDK v0.76.0:** [Release Notes](https://github.com/anthropics/anthropic-sdk-python/releases/tag/v0.76.0)

---

**Previous Release:** [v0.5.2](https://github.com/claude-php/Claude-PHP-SDK/releases/tag/v0.5.2) - Python SDK v0.76.0 Feature Parity  
**Repository:** [claude-php/Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)
