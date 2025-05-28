<?php

declare(strict_types=1);

namespace Communication\Factory;

use Communication\Context\CommunicationContext;
use Communication\Context\CommunicationContextInterface;
use Communication\Context\EmailContext;
use Communication\Entity\Communication;
use Communication\Entity\CommunicationSettings;

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

        return new Communication($data['definitionId'], $context);
    }

    private function createContext(array $data): CommunicationContext
    {
        $channelContexts = $this->getChannelContexts($data);
        $context = new CommunicationContext($channelContexts);
        $recipients = $this->getRecipients($data);
        $context->setFrom($this->settings->getFromAddress());
        $context->setRecipients($recipients);

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

    private function createChannelContext(string $channel, array $data): CommunicationContextInterface
    {
        return match ($channel) {
            'email' => new EmailContext(),
            default => throw new \RuntimeException("Unknown channel: $channel")
        };
    }

    private function getRecipients(array $data): array
    {
        return $data['recipients'] ?? [];
    }   
}
