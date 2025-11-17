# Contributing to Claude PHP SDK

Thank you for your interest in contributing to the Claude PHP SDK! We welcome pull requests, bug reports, and feature requests.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/claude-php-sdk.git`
3. Install dependencies: `composer install`
4. Create a feature branch: `git checkout -b feature/your-feature-name`

## Development Setup

```bash
# Install dependencies
composer install

# Run tests
composer test

# Check code style (PSR-12)
composer lint

# Auto-fix code style
composer format

# Run static analysis (PHPStan level 9)
composer stan
```

## Code Standards

- **PSR-12**: All code must follow PSR-12 coding standards
- **Types**: Use strict types and full type hints for all properties and methods
- **PHPStan**: All code must pass PHPStan analysis at level 9
- **Tests**: All features must have corresponding unit tests

## Before Submitting a Pull Request

1. Run all tests: `composer test`
2. Check code style: `composer lint`
3. Run static analysis: `composer stan`
4. Ensure code follows PSR-12 standards
5. Add tests for any new functionality
6. Update documentation if needed

## Pull Request Process

1. Update the CHANGELOG.md with your changes
2. Provide a clear description of what your PR does
3. Link any related issues
4. Ensure all CI checks pass
5. A maintainer will review your PR

## Reporting Issues

When reporting bugs, please include:
- PHP version
- SDK version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Error messages and stack traces

## Feature Requests

Feature requests are welcome! Please describe:
- The use case
- Why this feature would be useful
- Any alternative approaches you've considered

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Questions?

Feel free to open a GitHub Discussion or issue if you have questions.
