<?php

declare(strict_types=1);

namespace Communication\Factory\Notification;

use Communication\Context\CommunicationContextInterface;
use Communication\Context\EmailContext;
use Communication\Factory\Message\EmailMessageFactory;
use Communication\Notification\EmailNotification;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\Notification;

final class EmailNotificationFactory implements NotificationFactoryInterface
{
    public function __construct(
        private readonly EmailMessageFactory $emailMessageFactory,
    ) {
    }

    public function create(EmailContext|CommunicationContextInterface $communicationContext): Notification
    {
        $emailMessage = $this->emailMessageFactory->createMessage($communicationContext);

        if (!$emailMessage instanceof EmailMessage) {
            throw new \RuntimeException('Expected EmailMessage, got ' . get_class($emailMessage));
        }

        return new EmailNotification($emailMessage);
    }
}
