<?php

declare(strict_types=1);

namespace Communication\Context;

use Communication\Entity\Recipient;
use Symfony\Component\Mime\Address;

abstract class AbstractCommunicationContext implements CommunicationContextInterface
{
    /** @var \Symfony\Component\Mime\Address[] */
    protected array $bcc = [];

    protected array $bodyContext = [];

    protected ?Address $from = null;

    protected ?string $htmlTemplate = '';

    /** @var \Symfony\Component\Mime\Address[] */
    protected array $recipients = [];

    /** @var \Symfony\Component\Mime\Address[] */
    protected array $replyTo = [];

    protected string $subject = '';

    protected array $subjectContext = [];

    protected ?string $textTemplate = '';

    /**
     * @param mixed $value
     */
    public function addBodyContext(string $name, $value): static
    {
        $this->bodyContext[$name] = $value;

        return $this;
    }

    public function getBodyContext(): array
    {
        return $this->bodyContext;
    }

    public function setBodyContext(array $bodyContext): static
    {
        $this->bodyContext = $bodyContext;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function addSubjectContext(string $name, $value): static
    {
        $this->subjectContext[$name] = $value;

        return $this;
    }

    public function getSubjectContext(): array
    {
        return $this->subjectContext;
    }

    public function setSubjectContext(array $subjectContext): static
    {
        $this->subjectContext = $subjectContext;

        return $this;
    }

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function setBcc(array $bcc): static
    {
        $this->bcc = $this->extractAddresses($bcc);

        return $this;
    }

    public function getFrom(): ?Address
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from = null): static
    {
        if ($from) {
            $this->from = $this->extractAddress($from);
        } else {
            $this->from = null;
        }

        return $this;
    }

    public function getHtmlTemplate(): ?string
    {
        return $this->htmlTemplate;
    }

    public function setHtmlTemplate(?string $htmlTemplate): static
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
    public function setRecipients($recipients): static
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

    public function getReplyTo(): array
    {
        if (empty($this->replyTo)) {
            return $this->from ? [$this->from] : [];
        }

        return $this->replyTo;
    }

    public function setReplyTo(array $replyTo): static
    {
        $this->replyTo = $this->extractAddresses($replyTo);

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTextTemplate(): ?string
    {
        return $this->textTemplate;
    }

    public function setTextTemplate(?string $textTemplate): static
    {
        $this->textTemplate = $textTemplate;

        return $this;
    }

    /**
     * @param mixed $address
     */
    protected function extractAddress($address): Address
    {
        if ($address instanceof Recipient) {
            // Check if recipient has phone number (for SMS)
            try {
                $phone = $address->getPhone();
                if ($phone) {
                    // Convert phone number to email-like format for Address compatibility
                    $emailLikePhone = $this->phoneToEmailFormat($phone);
                    return new Address($emailLikePhone, $address->getName());
                }
            } catch (\Error) {
                // Phone property not initialized, fall back to email
            }

            return new Address($address->getEmail(), $address->getName());
        }

        if ($address instanceof Address) {
            return $address;
        }

        if (is_string($address)) {
            return Address::create($address);
        }

        if (is_array($address) && isset($address['email'])) {
            return new Address($address['email'], $address['name'] ?? '');
        }

        throw new \InvalidArgumentException('Invalid address type: ' . (is_object($address) ? get_class($address) : gettype($address)));
    }

    /**
     * Convert phone number to email-like format for Address compatibility
     */
    private function phoneToEmailFormat(string $phone): string
    {
        // Remove non-alphanumeric characters and convert to email-like format
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        $cleanPhone = str_replace('+', 'plus', $cleanPhone);
        return $cleanPhone . '@sms.internal';
    }

    protected function extractAddresses(array $addresses): array
    {
        $results = [];
        foreach ($addresses as $address) {
            $results[] = $this->extractAddress($address);
        }

        return $results;
    }
}
