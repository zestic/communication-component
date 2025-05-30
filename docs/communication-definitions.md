# Communication Definitions

[← Back to Documentation Home](index.md)

## Overview

Communication definitions allow you to define the structure and requirements for different types of communications. Each definition can have multiple channel definitions (email, mobile, etc.) with their own templates and validation schemas.

## Creating a Communication Definition

### Using the Factory

```php
// Create a factory for common communication types
$factory = new CommunicationDefinitionFactory();

// Get a pre-configured parcel arrival notification definition
$definition = $factory->createParcelArrivalDefinition();
```

### Creating Custom Definitions

```php
// Create a custom definition
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
```

### Adding Mobile Channel

```php
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

## Using Communication Definitions

### Validation

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

---

[← Back to Documentation Home](index.md)
