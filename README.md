# Communication Component

A flexible communication component that sends communications of any variety (email, SMS, chat) based on user preferences. Built on Symfony Notifier and Symfony Messenger.

## Features

- **Multi-channel Support**: Email, SMS, and chat communications
- **Asynchronous Processing**: Symfony Messenger integration for background processing
- **Template Management**: Database-stored templates with Twig support
- **Failure Handling**: Built-in retry mechanisms for failed messages
- **Database Migrations**: Phinx integration for schema management

## Quick Start

```bash
# Install dependencies
composer install

# Set up configuration
cp config/communication.global.php.dist config/communication.global.php

# Configure environment variables
cp .env.example .env

# Run migrations
bin/migrate

# Test email delivery
vendor/bin/laminas communication:send-test-email your-email@example.com
```

## Documentation

📚 **[Complete Documentation](docs/index.md)**

- [Getting Started](docs/getting-started.md) - Setup and basic configuration
- [Communication Definitions](docs/communication-definitions.md) - Structure different communication types
- [Messenger Integration](docs/messenger-integration.md) - Asynchronous processing
- [Database Migrations](docs/database-migrations.md) - Schema management with Phinx
- [Twig Database Loader](docs/twig-database-loader.md) - Dynamic template management
- [Handling Failures](docs/handling-failures.md) - Manage failed messages
- [Continuous Integration](docs/continuous-integration.md) - CI/CD setup

## Requirements

- PHP 8.1+
- Composer
- Database (PostgreSQL recommended)

## License

MIT License
