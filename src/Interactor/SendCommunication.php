<?php

declare(strict_types=1);

namespace Communication\Interactor;

use Communication\Definition\CommunicationDefinition;
use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\Repository\CommunicationDefinitionRepositoryInterface;
use Communication\Entity\Communication;
use Communication\Entity\Recipient;
use Communication\Factory\CommunicationFactory;
use Communication\Factory\Notification\NotificationFactoryInterface;
use Symfony\Component\Notifier\NotifierInterface;

class SendCommunication
{
    /**
     * @param array<string, NotificationFactoryInterface> $notificationFactories
     */
    public function __construct(
        private readonly CommunicationDefinitionRepositoryInterface $definitionRepository,
        private readonly array $notificationFactories,
        private readonly NotifierInterface $notifier,
        private readonly CommunicationFactory $communicationFactory,
    ) {
    }

    public function send(Communication|array $communication): void
    {
        if (is_array($communication)) {
            $communication = $this->communicationFactory->create($communication);
        }

        // Get the communication definition
        $definition = $this->definitionRepository->findByIdentifier($communication->getDefinitionId());
        if (!$definition) {
            throw new \RuntimeException("Communication definition not found: {$communication->getDefinitionId()}");
        }

        // Validate contexts against definition schemas
        $this->validateContexts($communication, $definition);

        // Apply templates from definition
        $this->applyTemplates($communication, $definition);

        // Send to each recipient
        foreach ($communication->getRecipients() as $recipient) {
            $this->sendToRecipient($recipient, $communication, $definition);
        }
    }

    private function validateContexts(Communication $communication, CommunicationDefinition $definition): void
    {
        $context = $communication->getContext();

        foreach ($definition->getChannelDefinitions() as $channelDefinition) {
            $channel = $channelDefinition->getChannel();
            $channelContext = $context->getContext($channel);
            if (!$channelContext) {
                continue;
            }

            // Validate context data against schema
            $contextData = $channelContext->getBodyContext();
            $channelDefinition->validateContext($contextData);

            // Validate subject against schema if applicable
            try {
                $subject = ['subject' => $channelContext->getSubject()];
                $channelDefinition->validateSubject($subject);
            } catch (\Error | \BadMethodCallException) {
                // Skip subject validation if getSubject method doesn't exist
            }
        }
    }

    private function applyTemplates(Communication $communication, CommunicationDefinition $definition): void
    {
        $context = $communication->getContext();

        foreach ($definition->getChannelDefinitions() as $channelDefinition) {
            $channel = $channelDefinition->getChannel();
            $channelContext = $context->getContext($channel);
            if (!$channelContext) {
                continue;
            }

            // Set template based on definition
            $template = $channelDefinition->getTemplate();

            // Determine template type (html, text, etc.) based on file extension or other logic
            $templateType = $this->determineTemplateType($template);
            $setter = 'set' . ucfirst(strtolower($templateType)) . 'Template';

            try {
                $channelContext->$setter($template);
            } catch (\Error | \BadMethodCallException) {
                // Skip setting template if method doesn't exist
            }

            // Set from address for email channel if applicable
            if ($channel === 'email' && $channelDefinition instanceof EmailChannelDefinition) {
                $fromAddress = $channelDefinition->getFromAddress();
                if ($fromAddress) {
                    try {
                        $channelContext->setFrom($fromAddress);
                    } catch (\Error | \BadMethodCallException) {
                        // Skip setting from address if method doesn't exist
                    }
                }
            }
        }
    }

    private function determineTemplateType(string $template): string
    {
        // Simple logic to determine template type based on file extension
        if (str_ends_with($template, '.html.twig')) {
            return 'html';
        } elseif (str_ends_with($template, '.text.twig')) {
            return 'text';
        }

        // Default to html
        return 'html';
    }

    private function sendToRecipient(Recipient $recipient, Communication $communication, CommunicationDefinition $definition): void
    {
        foreach ($recipient->getChannels() as $channel) {
            /** @var NotificationFactoryInterface $notificationFactory */
            $notificationFactory = $this->notificationFactories[$channel];
            $channelContext = $communication->getChannelContext($channel);
            if (!$channelContext) {
                continue;
            }
            $notification = $notificationFactory->create($channelContext);

            // Send notification
            $this->notifier->send($notification, $recipient);
        }
    }
}
