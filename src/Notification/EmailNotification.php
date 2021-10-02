<?php
declare(strict_types=1);

namespace Communication\Notification;

use Communication\Context\EmailContext;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

final class EmailNotification extends Notification implements EmailNotificationInterface
{
    /** @var \Symfony\Bridge\Twig\Mime\TemplatedEmail */
    private $email;

    public function __construct(EmailContext $emailContext, array $channels = [])
    {
        $this->createEmailMessage($emailContext);

        parent::__construct($emailContext->getSubject(), $channels);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        return new EmailMessage($this->email);
    }

    public function getEmail(): TemplatedEmail
    {
        return $this->email;
    }

    private function createEmailMessage(EmailContext $emailContext)
    {
        $email = (new TemplatedEmail())
            ->context($emailContext->getBodyContext())
            ->subject($emailContext->getSubject())
        ;
        if ($template = $emailContext->getHtmlTemplate()) {
            $email->htmlTemplate("$template.html.twig");
        }
        if ($template = $emailContext->getTextTemplate()) {
            $email->textTemplate("$template.text.twig");
        }
        foreach ($emailContext->getBcc() as $bcc) {
            $address = Address::create($bcc->getEmail());
            $email->addBcc($address);
        }
        foreach ($emailContext->getFrom() as $from) {
            $address = Address::create($from->getEmail());
            $email->addFrom($address);
        }
        foreach ($emailContext->getReplyTo() as $replyTo) {
            $address = Address::create($replyTo->getEmail());
            $email->addReplyTo($address);
        }
        foreach ($emailContext->getTo() as $to) {
            $address = Address::create($to->getEmail());
            $email->addTo($address);
        }

        $this->email = $email;
    }
}
