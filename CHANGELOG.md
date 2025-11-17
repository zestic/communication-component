# Changelog

## [v2.0.0-alpha.1] - 2025-11-16

### Added
- PostgreSQL support for template management
- Database migrations and seeds for communication and template entities
- Factory for SendCommunication
- Recommended levels of Twig templates to seed
- Comprehensive configuration reference in documentation
- Integration test environment variables and improved test setup
- Relaxed PHPStan checking for tests
- GitHub workflow for CI

### Changed
- Phinx configuration converted from YAML to PHP for better environment variable support
- Refactored factory classes to `Application` namespace
- Refactored communication contexts for better separation of concerns and ease of use
- Refactored database template handling and rendering
- Improved template repository and loader to handle namespaces
- Updated deprecated settings in command classes
- Improved documentation structure and content

### Fixed
- PHPStan errors and code style fixes
- Integration test database connection issues
- Symfony YAML version compatibility
- Test assertions and workflow test reliability
- Various code cleanups and formatting improvements

### Removed
- `.php-cs-fixer.cache` from git tracking

---

For previous changes, see earlier releases.
