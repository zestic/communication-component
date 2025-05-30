# Database Migrations

[← Back to Documentation Home](index.md)

## Overview

This component uses Phinx for database migrations with a PHP configuration file (`phinx.php`). The migrations are located in the `db/migrations` directory.

## Running Migrations

You can run migrations using the provided script:

```bash
# Run migrations in development environment (default)
bin/migrate

# Run a specific command (e.g., status, rollback)
bin/migrate status
bin/migrate rollback

# Run migrations in a specific environment
bin/migrate production
bin/migrate production rollback
```

## Running Seeds

To seed the database with initial data:

```bash
# Run all seeds
bin/migrate seed:run

# Run a specific seed
bin/migrate seed:run GenericCommunicationSeed

# Run seeds in a specific environment
bin/migrate production seed:run
bin/migrate production seed:run GenericCommunicationSeed

# Alternative using Phinx directly
vendor/bin/phinx seed:run -e development
vendor/bin/phinx seed:run -e development -s GenericCommunicationSeed
```

### GenericCommunicationSeed

The GenericCommunicationSeed creates a generic email communication definition and template with a simple body variable that can be used for testing.

## Creating New Migrations

To create a new migration:

```bash
vendor/bin/phinx create MyNewMigration
```

This will create a new migration file in the `db/migrations` directory.

## Available Environments

- **development**: For local development
- **testing**: For running tests
- **production**: For production deployment

## Additional Information

For more detailed information about the migrations, see the [db/README.md](../db/README.md) file.

## Migration Best Practices

- **Version Control**: Always commit migration files to version control
- **Test Migrations**: Test migrations in development before production
- **Backup**: Always backup production databases before running migrations
- **Rollback Plan**: Ensure you can rollback migrations if needed
- **Documentation**: Document complex migrations and their purpose

---

[← Back to Documentation Home](index.md)
