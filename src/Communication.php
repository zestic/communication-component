<?php

declare(strict_types=1);

namespace Communication;

use Communication\Context\CommunicationContext;
use Communication\Entity\Communication as CommunicationEntity;
use Communication\Interactor\SendCommunication;
use Symfony\Component\Mime\Address;

abstract class Communication
{
    /** @var Recipient[] */
    private array $recipients = [];

    public function __construct(
        protected CommunicationContext $context,
        private SendCommunication $sendCommunication,
    ) {
    }

    public function getDefinitionId(): string
    {
        return $this->getTemplates()['email']['html'];
    }

    public function getContext(): CommunicationContext
    {
        return $this->context;
    }

    /**
     * @return Recipient[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @param Recipient|Recipient[]|mixed $recipients
     */
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

    public function send(): void
    {
        $communication = new CommunicationEntity($this->getDefinitionId(), $this->context);
        $this->sendCommunication->send($communication);
    }

    public function setFrom(Recipient|Address|string $address): self
    {
        $this->context->setFrom($address);

        return $this;
    }

    abstract protected function getTemplates(): array;
}
