# Database Migrations

This directory contains database migrations for the Communication Component using Phinx.

## Directory Structure

- `migrations/`: Contains all the database migration files
- `seeds/`: Contains database seed files (if any)

## Migration Files

- `20250519101655_create_communication_definitions.php`: Creates the tables for communication definitions and channel definitions
- `20250519101717_create_communication_templates.php`: Creates the table for communication templates

## Seed Files

- `GenericCommunicationSeed.php`: Creates a generic email communication definition and template with a simple body variable

## Running Migrations

You can run migrations using the provided script:

```bash
# Run migrations in development environment (default)
bin/migrate

# Run migrations in a specific environment
bin/migrate production

# Run a specific command (e.g., rollback)
bin/migrate development rollback
```

## Running Seeds

To seed the database with initial data:

```bash
# Run all seeds
bin/migrate seed:run

# Run a specific seed
bin/migrate development seed:run GenericCommunicationSeed

# Alternative using Phinx directly
vendor/bin/phinx seed:run -e development
vendor/bin/phinx seed:run -e development -s GenericCommunicationSeed
```

## Creating New Migrations

To create a new migration:

```bash
vendor/bin/phinx create MyNewMigration
```

This will create a new migration file in the `db/migrations` directory.

## Configuration

The Phinx configuration is in `phinx.php` in the root directory. You can modify this file to change database connection settings or other Phinx options.

### Database Credentials

Before running migrations, make sure to update the database credentials in `phinx.php`:

```php
'development' => [
    'adapter' => 'pgsql',
    'host' => 'localhost',
    'name' => 'communication_development',
    'user' => 'your_postgres_username',
    'pass' => 'your_postgres_password',
    'port' => '5432',
    'charset' => 'utf8',
],
```

You can also use environment variables for sensitive information:

```php
'development' => [
    'adapter' => 'pgsql',
    'host' => getenv('DB_HOST') ?: 'localhost',
    'name' => getenv('DB_NAME') ?: 'communication_development',
    'user' => getenv('DB_USER') ?: 'postgres',
    'pass' => getenv('DB_PASSWORD') ?: 'postgres',
    'port' => getenv('DB_PORT') ?: '5432',
    'charset' => 'utf8',
],
```

## Available Environments

- `development`: For local development
- `testing`: For running tests
- `production`: For production deployment

## PostgreSQL Specific Notes

These migrations are specifically designed for PostgreSQL. They use PostgreSQL-specific features like:

- `jsonb` data type for storing JSON data
- Triggers for automatically updating the `updated_at` column
