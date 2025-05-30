# Getting Started

[← Back to Documentation Home](index.md)

## Quick Setup

The simplest setup is to have an email sent to an SMTP channel. Follow these steps to get started:

### 1. Configure the Component

Add the following to `config\config.php`:

```php
$aggregator = new ConfigAggregator([
    ...
    \Communication\ConfigProvider::class,
    ...
]);
```

### 2. Create Communication Configuration

Create `communication.global.php` and set up a simple route:

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

### 3. Environment Configuration

In your `.env` file, set the configuration for the email communication channel:

```bash
COMMUNICATION_TRANSPORT_EMAIL_TYPE=smtp
COMMUNICATION_TRANSPORT_EMAIL_AUTH_MODE=login
COMMUNICATION_TRANSPORT_EMAIL_ENCRYPTION=ssl
COMMUNICATION_TRANSPORT_EMAIL_USERNAME=********
COMMUNICATION_TRANSPORT_EMAIL_PASSWORD=********
COMMUNICATION_TRANSPORT_EMAIL_URI=smtp.mailtrap.io
COMMUNICATION_TRANSPORT_EMAIL_PORT=2525
```

### 4. Test the Setup

From the root of your project, run:

```bash
vendor/bin/laminas communication:send-test-email your-email@your-url.com --from=your-from-address@your-url.com
```

The email should be delivered to you.

## Next Steps

- Learn about [Communication Definitions](communication-definitions.md) for structured communications
- Set up [Messenger Integration](messenger-integration.md) for asynchronous processing
- Configure [Database Migrations](database-migrations.md) for persistent storage

---

[← Back to Documentation Home](index.md)
