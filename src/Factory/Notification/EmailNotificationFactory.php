<?php
declare(strict_types=1);

namespace Communication\Factory\Notification;

use Communication\Context\CommunicationContext;
use Communication\Notification\EmailNotification;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Notifier\Notification\Notification;

final class EmailNotificationFactory implements NotificationFactoryInterface
{
    public function __construct(
        private BodyRenderer $renderer
    ) {
    }

    public function create(CommunicationContext $context, string $channel): Notification
    {
        /** @var \Communication\Context\EmailContext $emailContext */
        $emailContext = $context->getMeta('email');
        $emailContext->setBodyContext($context->toArray());

        $communication = (new EmailNotification($emailContext, [$channel]));
        $this->renderEmail($communication);

        return $communication;
    }

    private function renderEmail(EmailNotification $communication)
    {
        $email = $communication->getEmail();

        $this->renderer->render($email);
    }
}
