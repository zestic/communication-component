# Twig Database Loader

[← Back to Documentation Home](index.md)

## Overview

The `TwigDatabaseLoader` allows you to store Twig templates in the database and load them dynamically. This is useful for applications where templates need to be managed through an admin interface or updated without deploying code changes.

## Basic Setup

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

## Chaining with FilesystemLoader

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

## Template Naming Convention

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

## Template Inheritance and Includes

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

## Caching and Performance

The `TwigDatabaseLoader` includes built-in caching and dependency tracking:

- **Template Caching**: Templates are cached in memory during the request
- **Dependency Tracking**: Automatically tracks template inheritance and includes
- **Cache Invalidation**: Cache keys include timestamps of templates and their dependencies
- **Freshness Checking**: Efficiently determines if templates need recompilation

## Database Schema

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

## Example Usage in Communication Component

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

---

[← Back to Documentation Home](index.md)
