# Upgrading to Communication Component v2

[← Back to Documentation Home](index.md)

## Overview

Communication Component v2 introduces breaking changes to configuration and database support. This guide walks through migrating from v1 to v2, with emphasis on database driver compatibility and configuration changes.

## Database Support

**v2 now officially supports both MySQL and PostgreSQL.** The component automatically detects your database driver and uses the appropriate repository implementation.

### Supported Database Drivers

| Database | Driver Name(s) | Status |
|----------|---|---|
| PostgreSQL | `pgsql`, `pdo_pgsql`, `postgres`, `postgresql` | ✅ Supported |
| MySQL | `mysql`, `pdo_mysql` | ✅ Supported |
| SQLite | `sqlite`, `pdo_sqlite` | ❌ Not supported |
| MariaDB | `mysql` (reported as mysql) | ✅ Supported |

If you use an unsupported database, the component will throw a clear error on startup.

---

## Configuration Breaking Changes

### v1 Configuration (Deprecated)

```php
'communication' => [
    'context' => [ // ❌ DEPRECATED
        'email' => EmailContext::class,
        'sms' => SmsContext::class,
    ],
]
```

### v2 Configuration (Required)

```php
'communication' => [
    'channelContexts' => [ // ✅ REQUIRED
        'email' => EmailContext::class,
        'sms' => SmsContext::class,
    ],
]
```

### Migration Steps

#### 1. Update Configuration Key

**Before (v1):**
```php
'communication' => [
    'context' => [
        'email' => EmailContext::class,
        'sms' => SmsContext::class,
    ],
]
```

**After (v2):**
```php
'communication' => [
    'channelContexts' => [
        'email' => EmailContext::class,
        'sms' => SmsContext::class,
    ],
]
```

#### 2. What Changed

| Aspect | v1 | v2 |
|--------|---|---|
| Config key | `communication.context` | `communication.channelContexts` |
| Repository alias | Manual configuration | Automatic driver detection |
| MySQL support | Not supported | Fully supported |
| PostgreSQL support | Supported | Fully supported |

#### 3. Error Messages

If you forget to update your configuration, v2 will fail fast with actionable errors:

**Missing `channelContexts`:**
```
RuntimeException: Missing required configuration key 'communication.channelContexts'. 
This is a v2-required configuration. See docs/UPGRADE-v2.md for migration steps.
```

**Using old `context` key:**
```
RuntimeException: Configuration key 'communication.context' is no longer supported in v2.
Replace with 'communication.channelContexts'. See docs/UPGRADE-v2.md for details.
```

---

## Database Repository Configuration

### v1 (Manual Configuration)

In v1, you had to manually configure the repository:

```php
'dependencies' => [
    'aliases' => [
        CommunicationDefinitionRepositoryInterface::class => 
            PostgresCommunicationDefinitionRepository::class,
    ],
]
```

### v2 (Automatic Detection)

In v2, the repository is **automatically selected by database driver**. No manual configuration needed:

```php
'dependencies' => [
    'factories' => [
        CommunicationDefinitionRepositoryInterface::class => 
            CommunicationDefinitionRepositoryFactory::class,
    ],
]
```

The factory reads your PDO connection's driver and resolves:
- `mysql`, `pdo_mysql` → `MySqlCommunicationDefinitionRepository`
- `pgsql`, `pdo_pgsql` → `PostgresCommunicationDefinitionRepository`

---

## Migration Examples

### Example 1: PostgreSQL Application (No Changes to DB Config)

**Before (v1):**
```php
return [
    'communication' => [
        'context' => [
            'email' => \Communication\Context\EmailContext::class,
        ],
        'channel' => [
            'email' => [
                'transport' => 'communication.channel.transport.email',
            ],
        ],
    ],
    'dependencies' => [
        'aliases' => [
            CommunicationDefinitionRepositoryInterface::class => 
                PostgresCommunicationDefinitionRepository::class,
        ],
    ],
];
```

**After (v2):**
```php
return [
    'communication' => [
        'channelContexts' => [  // ✅ CHANGED
            'email' => \Communication\Context\EmailContext::class,
        ],
        'channel' => [
            'email' => [
                'transport' => 'communication.channel.transport.email',
            ],
        ],
    ],
    'dependencies' => [
        'factories' => [  // ✅ CHANGED to factory-based
            CommunicationDefinitionRepositoryInterface::class => 
                CommunicationDefinitionRepositoryFactory::class,
        ],
    ],
];
```

### Example 2: MySQL Application (New in v2)

**v2 Configuration:**
```php
return [
    'communication' => [
        'channelContexts' => [
            'email' => \Communication\Context\EmailContext::class,
        ],
        'channel' => [
            'email' => [
                'transport' => 'communication.channel.transport.email',
            ],
        ],
    ],
    'dependencies' => [
        'factories' => [
            CommunicationDefinitionRepositoryInterface::class => 
                CommunicationDefinitionRepositoryFactory::class,
        ],
    ],
];
```

**Environment Configuration (.env):**
```bash
# MySQL Database
DB_HOST=localhost
DB_NAME=your_app_db
DB_USER=mysql_user
DB_PASSWORD=mysql_password
DB_PORT=3306
```

**That's it!** The factory auto-detects MySQL from the PDO driver and uses `MySqlCommunicationDefinitionRepository`.

### Example 3: Migrating from Manual Override

If your v1 app had a custom MySQL workaround:

**Before (v1 workaround):**
```php
'dependencies' => [
    'aliases' => [
        CommunicationDefinitionRepositoryInterface::class => 
            MyCustomMySqlRepository::class,  // ❌ Manual override
    ],
]
```

**After (v2):**
```php
'dependencies' => [
    'factories' => [
        CommunicationDefinitionRepositoryInterface::class => 
            CommunicationDefinitionRepositoryFactory::class,  // ✅ Automatic
    ],
]
// Remove MyCustomMySqlRepository — no longer needed!
```

---

## Checklist for Upgrade

- [ ] Update all `communication.context` → `communication.channelContexts` in config files
- [ ] Change repository `aliases` → `factories` with `CommunicationDefinitionRepositoryFactory`
- [ ] Test with your database (PostgreSQL or MySQL will auto-detect)
- [ ] Remove any custom MySQL workarounds from your application config
- [ ] Run tests to verify communication definitions load correctly
- [ ] Deploy to staging environment first

---

## Rollback (If Needed)

If you need to stay on v1:

```bash
composer require zestic/communication-component:^1.0
```

Then revert your configuration changes to use `communication.context` and manual repository aliases.

---

## Support

If you encounter issues during migration:

1. **Check error messages** — v2 errors are designed to be actionable
2. **Verify database driver** — Ensure `PDO::ATTR_DRIVER_NAME` returns `mysql` or `pgsql`
3. **Check phpunit.xml** — Ensure test environment variables match your database
4. **Review CI configuration** — Both MySQL and PostgreSQL tests should pass

See [Configuration Reference](configuration.md) for full details.
