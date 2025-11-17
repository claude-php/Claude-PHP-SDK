# Scripts Directory

This directory contains development scripts for the Claude PHP SDK, mirroring the functionality of the [Python SDK scripts](https://github.com/anthropics/anthropic-sdk-python/tree/main/scripts).

## Available Scripts

### Essential Development Scripts

#### `./scripts/bootstrap`
Sets up the development environment and installs dependencies.
```bash
./scripts/bootstrap
```
- Verifies PHP 8.1+ installation
- Installs Composer dependencies
- Validates development tools availability
- Provides usage instructions

#### `./scripts/format`
Formats code using PHP-CS-Fixer and PSR-12 standards.
```bash
./scripts/format
```
- Formats `src/` and `tests/` directories
- Uses `.php-cs-fixer.php` configuration
- Shows diffs of changes made
- Runs PSR-12 compliance fixer as backup

#### `./scripts/lint`
Comprehensive code quality and static analysis checks.
```bash
./scripts/lint
```
- **PHP CodeSniffer**: PSR-12 compliance checking
- **PHPStan**: Static analysis at level 9
- **PHP-CS-Fixer**: Code style verification (dry-run)
- **Autoload**: Composer autoload integrity
- **Syntax**: PHP syntax error detection
- **TODO/FIXME**: Comment tracking (informational)

#### `./scripts/test`
Enhanced test runner with multiple PHP version support.
```bash
# Basic test run
./scripts/test

# With coverage reporting
./scripts/test --coverage

# Verbose output
./scripts/test --verbose

# Filter specific tests
./scripts/test --filter "TestClassName"

# Test with specific PHP version
./scripts/test --php-version 8.2
```
Features:
- Multi-PHP version testing (8.1, 8.2, 8.3)
- Coverage reporting (HTML + XML)
- Test filtering capabilities
- Comprehensive test verification

### Advanced Scripts

#### `./scripts/detect-breaking-changes`
Analyzes git changes for potential breaking API changes.
```bash
# Compare current branch to main
./scripts/detect-breaking-changes

# Compare specific refs
./scripts/detect-breaking-changes --base v1.0.0 --head HEAD

# Skip compatibility tests
./scripts/detect-breaking-changes --no-compatibility-check
```
Detects:
- Removed classes, methods, and properties
- Namespace changes
- Method signature modifications
- Autoload compatibility issues

## Quick Development Workflow

1. **Initial setup:**
   ```bash
   ./scripts/bootstrap
   ```

2. **Before committing:**
   ```bash
   ./scripts/format      # Fix formatting
   ./scripts/lint        # Check quality
   ./scripts/test        # Run tests
   ```

3. **Before releasing:**
   ```bash
   ./scripts/detect-breaking-changes
   ```

## Composer Integration

These scripts complement the Composer scripts defined in `composer.json`:

```bash
# Composer shortcuts (simpler but less featured)
composer test      # Basic PHPUnit
composer lint      # Basic PHPCS
composer format    # Basic PHP-CS-Fixer
composer stan      # Basic PHPStan

# Enhanced scripts (recommended)
./scripts/test     # Multi-version, coverage, filtering
./scripts/lint     # Comprehensive quality checks
./scripts/format   # Full formatting pipeline
```

## Configuration Files

- **`.php-cs-fixer.php`**: PHP-CS-Fixer configuration with modern PHP rules
- **`phpunit.xml`**: PHPUnit configuration (if present)
- **`phpstan.neon`**: PHPStan configuration (if present)

## Requirements

- PHP 8.1 or later
- Composer
- Git (for breaking change detection)

All development dependencies are managed through `composer.json` and installed via `./scripts/bootstrap`.

## Script Development

These scripts are designed to match the Python SDK's development workflow while leveraging PHP-specific tools and conventions. Each script includes:

- Comprehensive error handling
- Informative output with emojis
- Multiple tool integration
- Cross-platform compatibility
- Help documentation

## Contributing

When modifying scripts:
1. Test on multiple PHP versions
2. Ensure cross-platform compatibility
3. Follow existing output formatting conventions
4. Update this README if adding new scripts