# Configuration Reference

[← Back to Documentation Home](index.md)

## Overview

This document provides a comprehensive reference for all configuration options available in the Communication Component. Configuration is handled through PHP configuration files, environment variables, and dependency injection.

## Table of Contents

- [Environment Variables](#environment-variables)
- [Communication Configuration](#communication-configuration)
- [Transport Configuration](#transport-configuration)
- [Messenger Configuration](#messenger-configuration)
- [Channel Configuration](#channel-configuration)
- [Database Configuration](#database-configuration)
- [Complete Configuration Example](#complete-configuration-example)

## Environment Variables

### Core Communication Settings

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `COMMUNICATION_FROM_EMAIL` | Default "from" email address for all communications | `noreply@example.com` | No |
| `COMMUNICATION_FROM_NAME` | Default "from" name for all communications | `System` | No |

### Messenger Transport

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `COMMUNICATION_MESSENGER_TRANSPORT_DSN` | Symfony Messenger transport DSN | `doctrine://dbal-default?queue_name=email` | No |

### Email Transport Configuration

#### SMTP Transport

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `COMMUNICATION_TRANSPORT_EMAIL_TYPE` | Transport type (`smtp`, `postmark`, `amazonsmtp`) | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_URI` | SMTP server hostname | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_PORT` | SMTP server port | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_USERNAME` | SMTP username | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_PASSWORD` | SMTP password | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_ENCRYPTION` | Encryption type (`ssl`, `tls`) | - | No |
| `COMMUNICATION_TRANSPORT_EMAIL_AUTH_MODE` | Authentication mode (`login`, `plain`) | - | No |

#### Postmark Transport

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `COMMUNICATION_TRANSPORT_EMAIL_TYPE` | Must be `postmark` | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_SCHEME` | Postmark scheme (`api`, `smtp`) | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_USERNAME` | Postmark API token | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_HOST` | Postmark host | `default` | No |

#### Amazon SES SMTP Transport

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `COMMUNICATION_TRANSPORT_EMAIL_TYPE` | Must be `amazonsmtp` | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_USERNAME` | AWS Access Key ID | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_PASSWORD` | AWS Secret Access Key | - | Yes |
| `COMMUNICATION_TRANSPORT_EMAIL_REGION` | AWS region | - | Yes |

### Database Configuration

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `DB_HOST` | Database host | `localhost` | No |
| `DB_NAME` | Database name | - | Yes |
| `DB_USER` | Database username | - | Yes |
| `DB_PASSWORD` | Database password | - | Yes |
| `DB_PORT` | Database port | `5432` | No |
| `POSTGRES_SCHEMA` | PostgreSQL schema name | `communication_component` | No |

## Communication Configuration

### Basic Structure

```php
return [
    'communication' => [
        'routes' => [
            // Route configuration
        ],
        'channel' => [
            // Channel configuration
        ],
        'channelContexts' => [
            // Channel context mappings
        ],
    ],
];
```

### Routes Configuration

Routes determine how communications are processed:

```php
'routes' => [
    'email' => 'channel',  // Direct channel processing
    'email' => 'bus',      // Asynchronous processing via message bus
],
```

**Options:**
- `'channel'` - Process communications synchronously
- `'bus'` - Process communications asynchronously via Symfony Messenger

### Channel Configuration

```php
'channel' => [
    'email' => [
        'factory' => EmailNotificationFactory::class,
        'transport' => 'communication.channel.transport.email',
        'channel' => 'communication.channel.email', // Optional: custom channel service
    ],
],
```

### Channel Contexts

```php
'channelContexts' => [
    'email' => EmailContext::class,
    // Add other channel contexts as needed
],
```

## Transport Configuration

Transport configuration is handled through the ConfigValue library, which reads from environment variables or configuration arrays.

### Configuration Keys

For email transports, configuration is read from the key: `communication.channel.transport.email`

### SMTP Transport Example

```php
'communication.channel.transport.email' => [
    'type' => 'smtp',
    'uri' => 'smtp.example.com',
    'port' => 587,
    'username' => 'your-username',
    'password' => 'your-password',
    'logger' => 'optional-logger-service', // Optional
],
```

### Postmark Transport Example

```php
'communication.channel.transport.email' => [
    'type' => 'postmark',
    'scheme' => 'api',
    'username' => 'your-postmark-token',
    'host' => 'default', // Optional
    'logger' => 'optional-logger-service', // Optional
],
```

### Amazon SES SMTP Transport Example

```php
'communication.channel.transport.email' => [
    'type' => 'amazonsmtp',
    'username' => 'your-aws-access-key',
    'password' => 'your-aws-secret-key',
    'region' => 'us-east-1',
],
```

## Messenger Configuration

The component includes pre-configured Symfony Messenger settings:

### Default Configuration

```php
'symfony' => [
    'messenger' => [
        'routing' => [
            SendEmailMessage::class => 'communication.bus.transport.email',
        ],
        'buses' => [
            'communication.bus.email' => [
                'allows_zero_handlers' => true,
                'handler_locator' => CommunicationBusLocator::class,
                'handlers' => [
                    SendEmailMessage::class => ['communication.bus.handler.email'],
                ],
                'middleware' => [
                    'communication.bus.email.bus-stamp-middleware',
                    'communication.bus.email.sender-middleware',
                    'communication.bus.email.handler-middleware',
                ],
                'routes' => [
                    '*' => ['communication.bus.transport.email'],
                ],
            ],
        ],
        'failure_transport' => 'messenger.transport.failed',
        'transports' => [
            'communication.bus.transport.email' => [
                'dsn' => 'doctrine://dbal-default?queue_name=email',
                'serializer' => PhpSerializer::class,
                'options' => [],
                'retry_strategy' => [
                    'max_retries' => 3,
                    'delay' => 100,
                    'multiplier' => 2,
                    'max_delay' => 0,
                ],
            ],
            'messenger.transport.failed' => [
                'dsn' => 'doctrine://dbal-default?queue_name=email',
                'serializer' => PhpSerializer::class,
                'options' => [],
                'retry_strategy' => [
                    'max_retries' => 3,
                    'delay' => 1000,
                    'multiplier' => 2,
                    'max_delay' => 0,
                ],
            ],
        ],
    ],
],
```

### Transport DSN Examples

- **Doctrine DBAL**: `doctrine://dbal-default?queue_name=email`
- **Redis**: `redis://localhost:6379/messages`
- **RabbitMQ**: `amqp://guest:guest@localhost:5672/%2f/messages`
- **Amazon SQS**: `sqs://ACCESS_KEY:SECRET_KEY@default/queue_name`

## Channel Configuration

### Email Channel

The email channel requires a transport configuration:

```php
'communication.channel.email' => [
    'transport' => 'communication.channel.transport.email',
    'from' => 'optional-default-from@example.com', // Optional
],
```

## Database Configuration

### Repository Aliases

```php
'aliases' => [
    CommunicationDefinitionRepositoryInterface::class => PostgresCommunicationDefinitionRepository::class,
    TemplateRepositoryInterface::class => PdoTemplateRepository::class,
],
```

### Phinx Configuration

Database migrations are handled by Phinx. Configuration in `phinx.php`:

```php
return [
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'pgsql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_NAME') ?: 'communication_development',
            'user' => getenv('DB_USER') ?: 'postgres',
            'pass' => getenv('DB_PASSWORD') ?: 'postgres',
            'port' => (int) (getenv('DB_PORT') ?: 5432),
            'charset' => 'utf8',
            'schema' => getenv('POSTGRES_SCHEMA') ?: 'communication_component',
        ],
        // ... other environments
    ],
];
```

## Complete Configuration Example

### .env File

```bash
# Communication settings
COMMUNICATION_FROM_EMAIL=noreply@yourcompany.com
COMMUNICATION_FROM_NAME="Your Company"

# Email transport (SMTP example)
COMMUNICATION_TRANSPORT_EMAIL_TYPE=smtp
COMMUNICATION_TRANSPORT_EMAIL_URI=smtp.mailgun.org
COMMUNICATION_TRANSPORT_EMAIL_PORT=587
COMMUNICATION_TRANSPORT_EMAIL_USERNAME=postmaster@mg.yourcompany.com
COMMUNICATION_TRANSPORT_EMAIL_PASSWORD=your-mailgun-password
COMMUNICATION_TRANSPORT_EMAIL_ENCRYPTION=tls
COMMUNICATION_TRANSPORT_EMAIL_AUTH_MODE=login

# Messenger transport
COMMUNICATION_MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages

# Database
DB_HOST=localhost
DB_NAME=your_app_production
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_PORT=5432
POSTGRES_SCHEMA=communication_component
```

### communication.global.php

```php
<?php

return [
    'communication' => [
        'routes' => [
            'email' => 'bus', // Use async processing
        ],
        'channel' => [
            'email' => [
                'factory' => \Communication\Factory\Notification\EmailNotificationFactory::class,
                'transport' => 'communication.channel.transport.email',
            ],
        ],
        'channelContexts' => [
            'email' => \Communication\Context\EmailContext::class,
        ],
    ],
];
```

## Complete Configuration Array

Here's a comprehensive configuration array showing all available options:

```php
<?php

return [
    'communication' => [
        // Routing configuration - determines how communications are processed
        'routes' => [
            'email' => 'channel',  // Synchronous processing
            // OR
            'email' => 'bus',      // Asynchronous processing via Symfony Messenger
            // OR
            'email' => [           // Advanced routing with custom bus
                'bus' => 'custom.bus.email'
            ],
        ],

        // Channel configuration - defines available communication channels
        'channel' => [
            'email' => [
                'factory' => \Communication\Factory\Notification\EmailNotificationFactory::class,
                'transport' => 'communication.channel.transport.email',
                'channel' => 'communication.channel.email', // Optional: custom channel service
                'messenger' => 'email', // Optional: messenger configuration
            ],
            // Add additional channels here (SMS, push notifications, etc.)
        ],

        // Channel context mappings - maps channels to their context classes
        'channelContexts' => [
            'email' => \Communication\Context\EmailContext::class,
            // Add other channel contexts as needed
        ],
    ],

    // Email channel configuration
    'communication.channel.email' => [
        'transport' => 'communication.channel.transport.email',
        'from' => 'default-sender@example.com', // Optional default from address
    ],

    // Transport configuration for email
    'communication.channel.transport.email' => [
        // SMTP Configuration
        'type' => 'smtp',
        'uri' => 'smtp.example.com',
        'port' => 587,
        'username' => 'your-smtp-username',
        'password' => 'your-smtp-password',
        'encryption' => 'tls', // Optional: ssl, tls
        'auth_mode' => 'login', // Optional: login, plain
        'logger' => 'optional-logger-service', // Optional PSR-3 logger service

        // OR Postmark Configuration
        'type' => 'postmark',
        'scheme' => 'api', // api or smtp
        'username' => 'your-postmark-server-token',
        'host' => 'default', // Optional
        'logger' => 'optional-logger-service', // Optional

        // OR Amazon SES SMTP Configuration
        'type' => 'amazonsmtp',
        'username' => 'your-aws-access-key-id',
        'password' => 'your-aws-secret-access-key',
        'region' => 'us-east-1',
    ],

    // Symfony Messenger configuration
    'symfony' => [
        'messenger' => [
            'routing' => [
                \Symfony\Component\Mailer\Messenger\SendEmailMessage::class => 'communication.bus.transport.email',
            ],
            'buses' => [
                'communication.bus.email' => [
                    'allows_zero_handlers' => true,
                    'handler_locator' => \Communication\Locator\CommunicationBusLocator::class,
                    'handlers' => [
                        \Symfony\Component\Mailer\Messenger\SendEmailMessage::class => ['communication.bus.handler.email'],
                    ],
                    'middleware' => [
                        'communication.bus.email.bus-stamp-middleware',
                        'communication.bus.email.sender-middleware',
                        'communication.bus.email.handler-middleware',
                    ],
                    'routes' => [
                        '*' => ['communication.bus.transport.email'],
                    ],
                ],
            ],
            'failure_transport' => 'messenger.transport.failed',
            'transports' => [
                'communication.bus.transport.email' => [
                    'dsn' => 'doctrine://dbal-default?queue_name=email',
                    // Alternative DSN examples:
                    // 'dsn' => 'redis://localhost:6379/messages',
                    // 'dsn' => 'amqp://guest:guest@localhost:5672/%2f/messages',
                    // 'dsn' => 'sqs://ACCESS_KEY:SECRET_KEY@default/queue_name',
                    'serializer' => \Symfony\Component\Messenger\Transport\Serialization\PhpSerializer::class,
                    'options' => [],
                    'retry_strategy' => [
                        'max_retries' => 3,
                        'delay' => 100,        // milliseconds
                        'multiplier' => 2,
                        'max_delay' => 0,      // 0 = no limit
                    ],
                ],
                'messenger.transport.failed' => [
                    'dsn' => 'doctrine://dbal-default?queue_name=failed',
                    'serializer' => \Symfony\Component\Messenger\Transport\Serialization\PhpSerializer::class,
                    'options' => [],
                    'retry_strategy' => [
                        'max_retries' => 3,
                        'delay' => 1000,      // milliseconds
                        'multiplier' => 2,
                        'max_delay' => 0,
                    ],
                ],
            ],
        ],
    ],

    // Dependency injection configuration
    'dependencies' => [
        'aliases' => [
            \Communication\Definition\Repository\CommunicationDefinitionRepositoryInterface::class =>
                \Communication\Definition\Repository\PostgresCommunicationDefinitionRepository::class,
            \Communication\Template\TemplateRepositoryInterface::class =>
                \Communication\Template\PdoTemplateRepository::class,
        ],
        'factories' => [
            // Core services
            \Symfony\Bridge\Twig\Mime\BodyRenderer::class =>
                \Communication\Application\Factory\BodyRendererFactory::class,
            \Communication\Factory\Context\ChannelContextFactory::class =>
                \Communication\Application\Factory\Factory\Context\ChannelContextFactoryFactory::class,
            \Communication\Entity\CommunicationSettings::class =>
                \Communication\Application\Factory\Entity\CommunicationSettingsFactory::class,
            \Symfony\Contracts\EventDispatcher\EventDispatcherInterface::class =>
                \Communication\Application\Factory\EventDispatcherFactory::class,
            \Symfony\Component\Notifier\NotifierInterface::class =>
                \Communication\Application\Factory\NotifierFactory::class,
            \Communication\Interactor\SendCommunication::class =>
                \Communication\Application\Factory\Interactor\SendCommunicationFactory::class,

            // Bus and transport configuration
            'communication.bus.email' => new \Netglue\PsrContainer\Messenger\Container\MessageBusStaticFactory(
                'communication.bus.email'
            ),
            'communication.bus.handler.email' => new \Communication\Application\Factory\MessageHandlerFactory(
                'communication.channel.transport.email'
            ),
            'communication.channel.email' => new \Communication\Application\Factory\Channel\EmailChannelFactory(
                'communication.channel.email'
            ),
            'communication.channel.transport.email' => new \Communication\Application\Factory\Transport\CommunicationTransportFactory(
                'communication.channel.transport.email'
            ),
            \Communication\Locator\CommunicationBusLocator::class =>
                new \Communication\Application\Factory\EmailBusLocatorFactory('communication.bus.email'),
        ],
        'abstract_factories' => [
            \Communication\Factory\Legacy\CommunicationFactory::class,
        ],
    ],

    // Console commands configuration
    'laminas-cli' => [
        'commands' => [
            'communication:send-test-email' => \Communication\Command\SendTestEmailCommand::class,
        ],
    ],
];
```

### Configuration Notes

1. **Environment Variable Integration**: Most configuration values can be overridden using environment variables through the ConfigValue library.

2. **Transport Types**: The `type` field in transport configuration determines which factory is used:
   - `smtp` → `SmtpFactory`
   - `postmark` → `PostmarkFactory`
   - `amazonsmtp` → `AmazonsmtpFactory`

3. **Routing Options**:
   - `'channel'` - Direct synchronous processing
   - `'bus'` - Asynchronous processing via Symfony Messenger
   - `['bus' => 'custom.bus.name']` - Custom bus configuration

4. **Messenger Transports**: The DSN determines the transport backend:
   - Doctrine DBAL: `doctrine://connection-name?queue_name=queue`
   - Redis: `redis://host:port/messages`
   - RabbitMQ: `amqp://user:pass@host:port/vhost/queue`
   - Amazon SQS: `sqs://ACCESS_KEY:SECRET_KEY@region/queue`

5. **Retry Strategy**: Configure how failed messages are retried:
   - `max_retries`: Maximum number of retry attempts
   - `delay`: Initial delay in milliseconds
   - `multiplier`: Exponential backoff multiplier
   - `max_delay`: Maximum delay (0 = no limit)

## Advanced Configuration

### Custom Transport Factory

To create a custom transport, implement the factory pattern:

```php
class CustomTransportFactory
{
    public function __construct(private string $configKey) {}

    public function __invoke(ContainerInterface $container): TransportInterface
    {
        $config = (new GatherConfigValues())($container, $this->configKey);
        // Create and return your custom transport
        return new CustomTransport($config);
    }
}
```

### Custom Channel Context

```php
class CustomChannelContext implements CommunicationContextInterface
{
    // Implement required methods
}

// Register in configuration
'channelContexts' => [
    'custom' => CustomChannelContext::class,
],
```

---

[← Back to Documentation Home](index.md)
