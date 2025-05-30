# Introduction

[← Back to Documentation Home](index.md)

## Communication Component

This component sends communications of any variety, email, sms, chat based on the user's preferences. Under the hood, it is using Symfony Notifier and Symfony Messenger.

## Key Features

- **Multi-channel Support**: Send communications via email, SMS, and chat
- **User Preferences**: Respect user communication preferences
- **Asynchronous Processing**: Use Symfony Messenger for background processing
- **Template Management**: Store and manage templates in database or filesystem
- **Failure Handling**: Built-in retry mechanisms for failed messages
- **Database Migrations**: Phinx integration for schema management
- **Flexible Configuration**: Easy setup and customization

## Architecture

The component is built on top of:
- **Symfony Notifier**: For multi-channel communication delivery
- **Symfony Messenger**: For asynchronous message processing
- **Twig**: For template rendering
- **Phinx**: For database migrations

## Future Plans

- Some refactoring to decrease complexity
- Allow part of context to be overridden by the Recipient

---

[← Back to Documentation Home](index.md)
