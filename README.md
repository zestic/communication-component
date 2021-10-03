# Communication Component

This component sends communications of any variety, email, sms, chat based on the 
user's preferences. Under the hood, it is using Symfony Notifier and Symfony Messenger.

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
            'email' => [
                'communication.channel.email',
            ],
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

Now from the root of your project
```bash
vendor/bin/laminas communication:send-test-email your-email@your-url.com --from=your-from-address@your-url.com
```

The email should be delivered to you.

