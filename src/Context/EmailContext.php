<?php

declare(strict_types=1);

namespace Communication\Context;

use Communication\Entity\Recipient;
use Symfony\Component\Mime\Address;

class EmailContext implements CommunicationContextInterface
{
    /** @var \Symfony\Component\Mime\Address[] */
    private array $bcc = [];

    private string $body = '';

    private array $bodyContext = [];

    /** @var \Symfony\Component\Mime\Address[] */
    private array $cc = [];

    private ?Address $from = null;

    private ?string $htmlTemplate = '';

    /** @var \Symfony\Component\Mime\Address[] */
    private array $recipients = [];

    /** @var \Symfony\Component\Mime\Address[] */
    private array $replyTo = [];

    private string $subject = '';

    private ?string $textTemplate = '';

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

    /**
     * @param mixed $value
     */
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

    /**
     * @param mixed $from
     */
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

    /**
     * @param mixed $recipients
     */
    public function setRecipients($recipients): EmailContext
    {
        if (is_array($recipients)) {
            $processedRecipients = [];
            foreach ($recipients as $recipient) {
                $processedRecipients[] = $this->extractAddress($recipient);
            }
            $this->recipients = $processedRecipients;
        }

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

    /**
     * @param mixed $address
     */
    private function extractAddress($address): Address
    {
        if ($address instanceof Recipient) {
            return new Address($address->getEmail(), $address->getName());
        }

        if ($address instanceof Address) {
            return $address;
        }

        if (is_string($address)) {
            return Address::create($address);
        }

        throw new \InvalidArgumentException('Invalid address type: ' . (is_object($address) ? get_class($address) : gettype($address)));
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
