# Handling Failures

[← Back to Documentation Home](index.md)

## Overview

When using Symfony Messenger for asynchronous processing, messages can fail for various reasons. The component provides tools to manage and retry failed messages.

## Viewing Failed Messages

### List All Failed Messages

To get a list of all failed messages:

```bash
vendor/bin/laminas messenger:failed:show -vv
```

### View Specific Failure Details

You can see details about a specific failure:

```bash
vendor/bin/laminas messenger:failed:show {id} -vv
```

Replace `{id}` with the actual message ID.

## Retrying Failed Messages

### Interactive Retry

View and retry messages one-by-one:

```bash
vendor/bin/laminas messenger:failed:retry -vv
```

This command will prompt you for each failed message, allowing you to decide whether to retry it.

### Retry Specific Messages

You can retry specific messages by their IDs:

```bash
vendor/bin/laminas messenger:failed:retry {id1} {id2} --force
```

Replace `{id1}`, `{id2}`, etc., with the actual message IDs.

### Retry All Failed Messages

To retry all failed messages at once:

```bash
vendor/bin/laminas messenger:failed:retry --force
```

**Warning**: Use this command carefully in production environments.

## Removing Failed Messages

### Remove Specific Messages

You can remove a message without retrying it:

```bash
vendor/bin/laminas messenger:failed:remove {id}
```

This permanently removes the message from the failed queue.

## Best Practices

- **Monitor Regularly**: Check failed messages regularly to identify patterns
- **Investigate Root Causes**: Don't just retry - understand why messages are failing
- **Set Up Alerts**: Configure monitoring to alert when failure rates are high
- **Log Analysis**: Use detailed logging to troubleshoot issues
- **Gradual Retry**: For bulk retries, consider processing in smaller batches

---

[← Back to Documentation Home](index.md)
