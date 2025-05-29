<?php

declare(strict_types=1);

namespace Communication\Context;

class EmailContext extends AbstractCommunicationContext
{
    private string $body = '';

    /** @var \Symfony\Component\Mime\Address[] */
    private array $cc = [];

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): EmailContext
    {
        $this->body = $body;

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

    public function getRecipientAddresses(): array
    {
        return $this->extractAddresses($this->recipients);
    }
}
