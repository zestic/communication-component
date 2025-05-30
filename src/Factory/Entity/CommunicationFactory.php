<?php

declare(strict_types=1);

namespace Communication\Factory\Entity;

use Communication\Context\CommunicationContext;
use Communication\Entity\Communication;
use Communication\Entity\CommunicationSettings;
use Communication\Entity\Recipient;
use Communication\Factory\Context\ChannelContextFactory;
use Symfony\Component\Mime\Address;

class CommunicationFactory
{
    public function __construct(
        private ChannelContextFactory $channelContextFactory,
        private CommunicationSettings $settings,
    ) {
    }

    public function create(array $data): Communication
    {
        $context = $this->createContext($data);
        $communication = new Communication($data['definitionId'], $context);

        // Add recipients to the communication object
        $recipients = $this->createRecipients($data);
        if (!empty($recipients)) {
            $communication->addRecipient($recipients);
        }

        return $communication;
    }

    private function createContext(array $data): CommunicationContext
    {
        $channelContexts = $this->getChannelContexts($data);
        $context = new CommunicationContext($channelContexts);
        $fromAddress = $this->getFromAddress($data);

        $context->setFrom($fromAddress);

        $this->setContextData($context, $data);

        return $context;
    }

    private function getChannelContexts(array $data): array
    {
        $channelContexts = [];
        foreach ($data['channels'] as $channel) {
            $channelContexts[$channel] = $this->channelContextFactory->create($channel);
        }

        return $channelContexts;
    }

    private function createRecipients(array $data): array
    {
        $recipientsData = $data['recipients'] ?? [];
        $recipients = [];

        foreach ($recipientsData as $recipientData) {
            $recipient = new Recipient();

            if (isset($recipientData['email'])) {
                $recipient->setEmail($recipientData['email']);
            }

            if (isset($recipientData['name'])) {
                $recipient->setName($recipientData['name']);
            }

            if (isset($recipientData['phone'])) {
                $recipient->setPhone($recipientData['phone']);
            }

            $recipients[] = $recipient;
        }

        return $recipients;
    }

    private function getFromAddress(array $data): Address
    {
        if (isset($data['from'])) {
            if (is_array($data['from'])) {
                $email = $data['from']['email'] ?? throw new \InvalidArgumentException('From array must contain "email" key');
                $name = $data['from']['name'] ?? '';

                return new Address($email, $name);
            }

            if (is_string($data['from'])) {
                return new Address($data['from']);
            }

            if ($data['from'] instanceof Address) {
                return $data['from'];
            }
        }

        return $this->settings->getFromAddress();
    }

    private function setContextData(CommunicationContext $context, array $data): void
    {
        if (!isset($data['context']) || !is_array($data['context'])) {
            return;
        }

        $contextData = $data['context'];

        // Set subject context if provided
        if (isset($contextData['subject']) && is_array($contextData['subject'])) {
            $context->setSubjectContext($contextData['subject']);
        }

        // Set body context if provided
        if (isset($contextData['body']) && is_array($contextData['body'])) {
            $context->setBodyContext($contextData['body']);
        }

        // Set channel-specific contexts
        foreach ($contextData as $channel => $channelData) {
            if (in_array($channel, ['subject', 'body']) || !is_array($channelData)) {
                continue;
            }

            $channelContext = $context->getContext($channel);
            if ($channelContext !== null) {
                foreach ($channelData as $key => $value) {
                    $channelContext->addBodyContext($key, $value);
                }
            }
        }
    }
}
