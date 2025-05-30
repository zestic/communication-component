# Messenger Integration

[← Back to Documentation Home](index.md)

## Overview

Use Symfony Messenger to handle the delivery of communications asynchronously. This allows for better performance and reliability in your application.

## Configuration

To use Messenger for email delivery, update the `communication.global.php` file:

```php
return [
    'communication' => [
        'routes' => [
            'email' => 'bus'
        ],
    ],
];
```

This will default to using the Doctrine transport.

## Transport Configuration

If you want to change the transport, add the transport configuration to your `.env` file:

```bash
COMMUNICATION_MESSENGER_TRANSPORT_DNS=redis://localhost:6379/messages
```

Refer to the [Symfony Messenger Transport Configuration documentation](https://symfony.com/doc/current/messenger.html#transport-configuration) for more information on available transport configurations.

## Testing Async Processing

Run the test command:

```bash
vendor/bin/laminas communication:send-test-email your-email@your-url.com --from=your-from-address@your-url.com
```

The command will complete, but you won't see the email in your inbox immediately. You need to start the message consumer.

## Starting the Consumer

Start the message consumer to process queued messages:

```bash
vendor/bin/laminas messenger:consume communication.bus.transport.email
```

This will start the consumer, and your test email will be processed and delivered.

## Consumer Management

See more information on how to set up your consumers in the [Symfony Messenger documentation](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker).

### Production Considerations

- Use process managers like Supervisor to keep consumers running
- Monitor consumer health and restart if needed
- Consider scaling consumers based on message volume
- Implement proper logging and monitoring

---

[← Back to Documentation Home](index.md)
