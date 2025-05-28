<?php

declare(strict_types=1);

namespace Communication\Factory\Notification;

use Communication\Context\CommunicationContextInterface;
use Communication\Context\EmailContext;
use Communication\Factory\Message\EmailMessageFactory;
use Communication\Notification\EmailNotification;
use Symfony\Component\Notifier\Notification\Notification;

final class EmailNotificationFactory implements NotificationFactoryInterface
{
    public function __construct(
        private readonly EmailMessageFactory $emailMessageFactory,
    ) {}

    public function create(EmailContext|CommunicationContextInterface $emailContext): Notification
    {
        $emailMessage = $this->emailMessageFactory->createMessage($emailContext);

        return new EmailNotification($emailMessage);
    }
}
