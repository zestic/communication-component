<?php

declare(strict_types=1);

namespace Communication\Notification;

use Communication\Context\EmailContext;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

final class EmailNotification extends Notification implements EmailNotificationInterface
{
    private EmailMessage $email;

    public function __construct(EmailContext $emailContext, array $channels = [])
    {
        $this->email = $emailContext->createMessage();

        parent::__construct($emailContext->getSubject(), $channels);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        return $this->email;
    }
}
