<?php
declare(strict_types=1);

namespace Communication\Factory\Notification;

use Communication\Context\CommunicationContext;
use Communication\Context\CommunicationContextInterface;
use Communication\Notification\EmailNotification;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Notifier\Notification\Notification;

final class EmailNotificationFactory implements NotificationFactoryInterface
{
    public function __construct(
        private BodyRenderer $renderer
    ) {
    }

    public function create(CommunicationContextInterface $emailContext, string $channel): Notification
    {
        $notification = (new EmailNotification($emailContext, [$channel]));
        $email = $notification->getEmail();
        $this->renderer->render($email);

        return $notification;
    }
}
