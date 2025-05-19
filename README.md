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

This component uses Phinx for database migrations. The migrations are located in the `db/migrations` directory.

### Running Migrations

You can run migrations using the provided script:

```bash
# Run migrations in development environment (default)
bin/migrate

# Run migrations in a specific environment
bin/migrate production

# Run a specific command (e.g., rollback)
bin/migrate development rollback
```

### Running Seeds

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

### Future plans
* Some refactoring to decrease complexity
* Allow part of context to be overridden by the Recipient
