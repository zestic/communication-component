<?php

declare(strict_types=1);

namespace Communication\Factory\Notification;

use Communication\Context\CommunicationContextInterface;
use Communication\Notification\EmailNotification;
use Symfony\Component\Notifier\Notification\Notification;

final class EmailNotificationFactory implements NotificationFactoryInterface
{
    public function create(CommunicationContextInterface $emailContext, string $channel): Notification
    {
        return new EmailNotification($emailContext, [$channel]);
    }
}
