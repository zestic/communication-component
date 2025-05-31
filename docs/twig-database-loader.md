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

The `TwigDatabaseLoader` uses standard Twig naming conventions for templates:

- **Format**: `template_name.extension.twig`
- **Examples**:
  - `welcome.html.twig` - Welcome template for HTML content
  - `notification.text.twig` - Notification template for text content
  - `invoice.html.twig` - Invoice template for HTML content

Templates are searched by their full name including the extension:

```php
// Render templates by their full names:
$twig->render('welcome.html.twig', $context);
$twig->render('notification.text.twig', $context);
```

## Template Inheritance and Includes

The loader supports Twig template inheritance and includes. When using `{% extends %}` or `{% include %}`, you can reference templates by their full names:

```twig
{# Extend a base template #}
{% extends "base.html.twig" %}

{# Extend a layout template #}
{% extends "email_layout.html.twig" %}

{# Include a template #}
{% include "header.html.twig" %}

{# Include with fallback #}
{% include "custom-header.html.twig" ignore missing %}
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
$emailContext->setHtmlTemplate('welcome.html.twig'); // Loads 'welcome.html.twig' from database
$emailContext->setTextTemplate('welcome.text.twig'); // Loads 'welcome.text.twig' from database

// Send communication
$communication = new Communication('user.welcome', $emailContext);
$sendCommunication->execute($communication);
```

---

[← Back to Documentation Home](index.md)
