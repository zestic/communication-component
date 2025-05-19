<?php

declare(strict_types=1);

namespace Communication;

use Communication\Context\CommunicationContext;
use Symfony\Component\Mime\Address;

class Communication
{
    /** @var Recipient[] */
    private array $recipients = [];

    public function __construct(
        private string $definitionId,
        private ?CommunicationContext $context = null
    ) {
        $this->context = $context ?? new CommunicationContext([]);
    }

    public function getDefinitionId(): string
    {
        return $this->definitionId;
    }

    public function getContext(): CommunicationContext
    {
        return $this->context;
    }

    public function addRecipient($recipients): self
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $this->context->setRecipients($recipients);

        foreach ($recipients as $recipient) {
            $this->recipients[] = $recipient;
        }

        return $this;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function setFrom(Recipient|Address|string $address): self
    {
        $this->context->setFrom($address);

        return $this;
    }
}
