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

    public function send()
    {
        $communication = new CommunicationEntity($this->getDefinitionId(), $this->context);
        $this->sendCommunication->send($communication);
    }

    public function setFrom(Recipient|Address|string $address)
    {
        $this->context->setFrom($address);
    }

    abstract protected function getTemplates(): array;
}
