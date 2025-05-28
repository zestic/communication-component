<?php

declare(strict_types=1);

namespace Communication\Notification;

use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

final class EmailNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(
        private EmailMessage $email,
    ) {
        parent::__construct($email->getSubject(), ['email']);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        return $this->email;
    }
}
