<?php

declare(strict_types=1);

namespace Communication\Factory\Message;

use Communication\Context\CommunicationContextInterface;
use Communication\Context\EmailContext;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Message\MessageInterface;

class EmailMessageFactory implements MessageFactoryInterface
{
    public function __construct(
        private BodyRenderer $renderer,
    ) {}

    public function createMessage(
        EmailContext|CommunicationContextInterface $emailContext,
    ): EmailMessage|MessageInterface {
        $email = (new TemplatedEmail())
            ->context($emailContext->getBodyContext())
            ->subject($emailContext->getSubject());
        if ($template = $emailContext->getHtmlTemplate()) {
            $email->htmlTemplate($template);
        }
        if ($template = $emailContext->getTextTemplate()) {
            $email->textTemplate($template);
        }
        foreach ($emailContext->getBcc() as $bcc) {
            $email->addBcc($bcc);
        }
        foreach ($emailContext->getReplyTo() as $replyTo) {
            $email->addReplyTo($replyTo);
        }
        if ($from = $emailContext->getFrom()) {
            $email->addFrom($from);
        }
        $this->renderer->render($email);

        return new EmailMessage($email);
    }
}
