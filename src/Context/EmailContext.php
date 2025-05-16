<?php

declare(strict_types=1);

namespace Communication\Context;

use Communication\Factory\Message\MessageFactoryInterface;
use Communication\Recipient;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Message\MessageInterface;

final class EmailContext implements CommunicationContextInterface
{
    /** @var \Symfony\Component\Mime\Address[] */
    private array $bcc = [];

    private string $body = '';

    private array $bodyContext = [];

    /** @var \Symfony\Component\Mime\Address[] */
    private array $cc = [];

    private ?Address $from = null;

    private string $htmlTemplate = '';

    /** @var \Symfony\Component\Mime\Address[] */
    private array $recipients;

    /** @var \Symfony\Component\Mime\Address[] */
    private array $replyTo = [];

    private string $subject = '';

    private string $textTemplate = '';

    public function __construct(
        private MessageFactoryInterface $emailMessageFactory,
    ) {
    }

    public function createMessage(): MessageInterface|EmailMessage
    {
        return $this->emailMessageFactory->createMessage($this);
    }

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function setBcc(array $bcc): EmailContext
    {
        $this->bcc = $this->extractAddresses($bcc);

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): EmailContext
    {
        $this->body = $body;

        return $this;
    }

    public function addBodyContext(string $name, $value): EmailContext
    {
        $this->bodyContext[$name] = $value;

        return $this;
    }

    public function getBodyContext(): array
    {
        return $this->bodyContext;
    }

    public function setBodyContext(array $bodyContext): EmailContext
    {
        $this->bodyContext = $bodyContext;

        return $this;
    }

    public function getCc(): array
    {
        return $this->cc;
    }

    public function setCc(array $cc): EmailContext
    {
        $this->cc = $this->extractAddresses($cc);

        return $this;
    }

    public function getFrom(): ?Address
    {
        return $this->from;
    }

    public function setFrom($from = null): EmailContext
    {
        if ($from) {
            $this->from = $this->extractAddress($from);
        }

        return $this;
    }

    public function getHtmlTemplate(): ?string
    {
        return $this->htmlTemplate;
    }

    public function setHtmlTemplate(?string $htmlTemplate): EmailContext
    {
        $this->htmlTemplate = $htmlTemplate;

        return $this;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function setRecipients($recipients): EmailContext
    {
        $this->recipients = $recipients;

        return $this;
    }

    public function getRecipientAddresses(): array
    {
        return $this->extractAddresses($this->recipients);
    }

    public function getReplyTo(): array
    {
        if (empty($this->replyTo)) {
            return $this->from ? [$this->from] : [];
        }

        return $this->replyTo;
    }

    public function setReplyTo(array $replyTo): EmailContext
    {
        $this->replyTo = $this->extractAddresses($replyTo);

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): EmailContext
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTextTemplate(): ?string
    {
        return $this->textTemplate;
    }

    public function setTextTemplate(?string $textTemplate): EmailContext
    {
        $this->textTemplate = $textTemplate;

        return $this;
    }

    private function extractAddress($address): Address
    {
        if ($address instanceof Recipient) {
            return new Address($address->getEmail(), $address->getName());
        }

        return Address::create($address);
    }

    private function extractAddresses(array $addresses): array
    {
        $results = [];
        foreach ($addresses as $address) {
            $results[] = $this->extractAddress($address);
        }

        return $results;
    }
}
