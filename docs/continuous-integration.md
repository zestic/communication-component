# Continuous Integration

[← Back to Documentation Home](index.md)

## Overview

This project uses GitHub Actions for continuous integration to ensure code quality and functionality.

## Workflows

### Lint Workflow

The lint workflow runs code quality checks:

- **PHP-CS-Fixer**: Ensures consistent code formatting
- **PHPStan**: Performs static analysis to catch potential issues

### Tests Workflow

The tests workflow runs the test suite:

- **PHPUnit Tests**: Runs unit and integration tests
- **PostgreSQL Integration**: Uses PostgreSQL for integration tests
- **Database Setup**: Automatically sets up test database with migrations

## Triggers

The workflows are automatically triggered on:

- **Push to main branch**: Ensures main branch always passes tests
- **Pull requests**: Validates changes before merging

## Configuration

The CI configuration files are located in `.github/workflows/`:

- `lint.yml`: Code quality checks
- `tests.yml`: Test execution

## Local Testing

Before pushing changes, you can run the same checks locally:

### Code Quality

```bash
# Run PHP-CS-Fixer
composer cs-fix

# Run PHPStan
composer phpstan
```

### Tests

```bash
# Run PHPUnit tests
composer test

# Run tests with coverage
composer test-coverage
```

## Best Practices

- **Green CI**: Keep the main branch green (all tests passing)
- **Fix Failures**: Address CI failures promptly
- **Local Testing**: Run tests locally before pushing
- **Code Quality**: Follow coding standards enforced by PHP-CS-Fixer
- **Static Analysis**: Address PHPStan warnings and errors

---

[← Back to Documentation Home](index.md)
