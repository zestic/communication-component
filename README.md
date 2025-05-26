# Communication Component

This component sends communications of any variety, email, sms, chat based on the
user's preferences. Under the hood, it is using Symfony Notifier and Symfony Messenger.

## Getting started
The simplest setup is to have an email sent to an SMTP channel. To set up the
configuration, add the following to `config\config.php`

```php
$aggregator = new ConfigAggregator([
    ...
    \Communication\ConfigProvider::class,
    ...
```

Next, create `communication.global.php` and set up a simple route.

```php
return [
    'communication' => [
        'routes' => [
            'email' => 'channel'
        ],
    ],
];
```

If you don't set the `from` address globally, you can also set it on a communication level.

In your .env file set the configuration for the email communication channel
```bash
COMMUNICATION_TRANSPORT_EMAIL_TYPE=smtp
COMMUNICATION_TRANSPORT_EMAIL_AUTH_MODE=login
COMMUNICATION_TRANSPORT_EMAIL_ENCRYPTION=ssl
COMMUNICATION_TRANSPORT_EMAIL_USERNAME=********
COMMUNICATION_TRANSPORT_EMAIL_PASSWORD=********
COMMUNICATION_TRANSPORT_EMAIL_URI=smtp.mailtrap.io
COMMUNICATION_TRANSPORT_EMAIL_PORT=2525
```

// todo: set up a generic template

Now from the root of your project
```bash
vendor/bin/laminas communication:send-test-email your-email@your-url.com --from=your-from-address@your-url.com
```

The email should be delivered to you.

## Communication Definitions

Communication definitions allow you to define the structure and requirements for different types of communications. Each definition can have multiple channel definitions (email, mobile, etc.) with their own templates and validation schemas.

### Creating a Communication Definition

```php
// Create a factory for common communication types
$factory = new CommunicationDefinitionFactory();

// Get a pre-configured parcel arrival notification definition
$definition = $factory->createParcelArrivalDefinition();

// Or create a custom definition
$definition = new CommunicationDefinition('custom.notification', 'Custom Notification');

// Add an email channel
$emailDef = new EmailChannelDefinition(
    'emails/custom.html.twig',
    [
        'type' => 'object',
        'required' => ['userId', 'message'],
        'properties' => [
            'userId' => ['type' => 'string'],
            'message' => ['type' => 'string']
        ]
    ],
    [
        'type' => 'object',
        'required' => ['title'],
        'properties' => [
            'title' => ['type' => 'string']
        ]
    ],
    'notifications@example.com'
);

$definition->addChannelDefinition($emailDef);

// Add a mobile channel
$mobileDef = new MobileChannelDefinition(
    'mobile/custom.json',
    [
        'type' => 'object',
        'required' => ['message'],
        'properties' => [
            'message' => ['type' => 'string']
        ]
    ],
    [
        'type' => 'object',
        'required' => ['title'],
        'properties' => [
            'title' => ['type' => 'string']
        ]
    ],
    1, // High priority
    false // No auth required
);

$definition->addChannelDefinition($mobileDef);
```

### Using Communication Definitions

```php
// Validate context for email channel
$emailContext = [
    'userId' => 'user123',
    'message' => 'Hello, World!'
];

$emailDef->validateContext($emailContext); // Throws InvalidContextException if invalid

// Validate subject for email channel
$emailSubject = ['title' => 'Welcome'];
$emailDef->validateSubject($emailSubject); // Throws InvalidSubjectException if invalid
```

## Using Messenger

To use Messenger to help handle the delivery of the emails simply update the `communication.global.php` file.
```php
return [
    'communication' => [
        'routes' => [
            'email' => 'bus'
        ],
    ],
];
```

This will default to using the Doctrine transport. If you want to change the transort, in your .env file add the
transport.
```bash
COMMUNICATION_MESSENGER_TRANSPORT_DNS=redis://localhost:6379/messages
```

Refer to the [Symfony Messenger Transport Configuration documentation](https://symfony.com/doc/current/messenger.html#transport-configuration)
for more information on available transport configurations.

Once again run
```bash
vendor/bin/laminas communication:send-test-email your-email@your-url.com --from=your-from-address@your-url.com
```

The command will complete, however, if you check, you will not see the email in your inbox. That's because you need to
start the message consumer. That is a pretty straight forward process.

```bash
vendor/bin/laminas messenger:consume communication.bus.transport.email
```

This will start the consumer. Your test email will now be in your inbox.

See more information on how to set up your consumers in the
[Symfony Messenger documentation](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker)

## Handling Failures

To get a list of the failed messages, run this command
```bash
vendor/bin/laminas messenger:failed:show -vv
```

You can see details about a specific failure
```bash
vendor/bin/laminas messenger:failed:show {id} -vv
```

You can view and retry messages one-by-one
```bash
vendor/bin/laminas messenger:failed:retry -vv
```

You can retry specific messages
```bash
vendor/bin/laminas messenger:failed:retry {id1} {id2} --force
```

You can retry all messages at once
```bash
vendor/bin/laminas messenger:failed:retry --force
```

You can also remove a message without retrying it
```bash
vendor/bin/laminas messenger:failed:remove {id}
```

## Database Migrations

This component uses Phinx for database migrations with a YAML configuration file (`phinx.yml`). The migrations are located in the `db/migrations` directory.

### Running Migrations

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

### Running Seeds

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

The GenericCommunicationSeed creates a generic email communication definition and template with a simple body variable that can be used for testing.

### Creating New Migrations

To create a new migration:

```bash
vendor/bin/phinx create MyNewMigration
```

This will create a new migration file in the `db/migrations` directory.

### Available Environments

- `development`: For local development
- `testing`: For running tests
- `production`: For production deployment

For more information about the migrations, see the [db/README.md](db/README.md) file.

## TwigDatabaseLoader

The `TwigDatabaseLoader` allows you to store Twig templates in the database and load them dynamically. This is useful for applications where templates need to be managed through an admin interface or updated without deploying code changes.

### Basic Setup

To use the `TwigDatabaseLoader` as your primary template loader:

```php
use Communication\Template\TwigDatabaseLoader;
use Communication\Template\TemplateRepositoryInterface;
use Twig\Environment;

// Assuming you have a template repository configured
$templateRepository = $container->get(TemplateRepositoryInterface::class);

// Create the database loader
$databaseLoader = new TwigDatabaseLoader($templateRepository);

// Create Twig environment with database loader
$twig = new Environment($databaseLoader, [
    'cache' => '/path/to/cache',
    'auto_reload' => true, // Recommended for development
]);
```

### Chaining with FilesystemLoader

For maximum flexibility, you can chain the `TwigDatabaseLoader` with Twig's `FilesystemLoader` using the `ChainLoader`. This allows templates to be loaded from both the database and the filesystem, with the database taking precedence:

```php
use Communication\Template\TwigDatabaseLoader;
use Communication\Template\TemplateRepositoryInterface;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

// Set up template repository
$templateRepository = $container->get(TemplateRepositoryInterface::class);

// Create loaders
$databaseLoader = new TwigDatabaseLoader($templateRepository);
$filesystemLoader = new FilesystemLoader([
    '/path/to/templates',
    '/path/to/fallback/templates'
]);

// Chain loaders (database loader has priority)
$chainLoader = new ChainLoader([
    $databaseLoader,    // First priority: database templates
    $filesystemLoader   // Fallback: filesystem templates
]);

// Create Twig environment
$twig = new Environment($chainLoader, [
    'cache' => '/path/to/cache',
    'auto_reload' => true,
]);
```

### Template Naming Convention

The `TwigDatabaseLoader` uses a specific naming convention for templates:

- **Format**: `template_name:channel`
- **Examples**:
  - `welcome:email` - Welcome template for email channel
  - `notification:mobile` - Notification template for mobile channel
  - `invoice:email` - Invoice template for email channel

If no channel is specified, it defaults to `email`:

```php
// These are equivalent:
$twig->render('welcome:email', $context);
$twig->render('welcome', $context);  // Defaults to email channel
```

### Template Inheritance and Includes

The loader supports Twig template inheritance and includes. When using `{% extends %}` or `{% include %}`, you can reference templates in several ways:

```twig
{# Extend a template in the same channel #}
{% extends "base" %}

{# Extend a template in a specific channel #}
{% extends "base:email" %}

{# Include a template #}
{% include "header:email" %}

{# Include with fallback #}
{% include "custom-header" ignore missing %}
```

### Caching and Performance

The `TwigDatabaseLoader` includes built-in caching and dependency tracking:

- **Template Caching**: Templates are cached in memory during the request
- **Dependency Tracking**: Automatically tracks template inheritance and includes
- **Cache Invalidation**: Cache keys include timestamps of templates and their dependencies
- **Freshness Checking**: Efficiently determines if templates need recompilation

### Database Schema

Templates are stored in the `communication_templates` table with the following structure:

```sql
CREATE TABLE communication_templates (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    channel VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    content_type VARCHAR(100) DEFAULT 'text/html',
    subject VARCHAR(255),
    metadata JSONB,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    UNIQUE(name, channel)
);
```

### Example Usage in Communication Component

When using with the Communication Component, templates are automatically loaded from the database:

```php
// Email context will use database templates
$emailContext = new EmailContext();
$emailContext->setHtmlTemplate('welcome'); // Loads 'welcome:email' from database
$emailContext->setTextTemplate('welcome'); // Loads 'welcome:email' text version

// Send communication
$communication = new Communication('user.welcome', $emailContext);
$sendCommunication->execute($communication);
```

### Future plans
* Some refactoring to decrease complexity
* Allow part of context to be overridden by the Recipient

## Continuous Integration

This project uses GitHub Actions for continuous integration:

- **Lint Workflow**: Runs PHP-CS-Fixer and PHPStan to ensure code quality
- **Tests Workflow**: Runs PHPUnit tests with PostgreSQL for integration tests

The workflows are automatically triggered on push to the main branch and on pull requests.
