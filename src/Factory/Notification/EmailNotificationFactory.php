<?php

declare(strict_types=1);

namespace Communication\Factory\Notification;

use Communication\Context\CommunicationContextInterface;
use Communication\Context\EmailContext;
use Communication\Notification\EmailNotification;
use Symfony\Component\Notifier\Notification\Notification;

final class EmailNotificationFactory implements NotificationFactoryInterface
{
    public function create(CommunicationContextInterface $emailContext, string $channel): Notification
    {
        if (!$emailContext instanceof EmailContext) {
            throw new \InvalidArgumentException(sprintf(
                'EmailNotificationFactory requires an EmailContext, got %s',
                get_class($emailContext)
            ));
        }

        return new EmailNotification($emailContext, [$channel]);
    }
}
