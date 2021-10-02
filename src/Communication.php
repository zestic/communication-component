<?php
declare(strict_types=1);

namespace Communication;

use Communication\Context\CommunicationContext;
use Communication\Factory\Communication\NotificationFactoryInterface;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

abstract class Communication
{
    /** @var \Notification\Context\NotificationContext */
    protected $context;
    /** @var \Notification\RecipientChannels[] */
    private array $recipientChannels = [];

    public function __construct(
        private NotifierInterface $notifier,
        CommunicationContext $context,
        private array $channels,
        private array $notificationFactories,
    ) {
        $this->context = $context;
    }

    public function getContext(): CommunicationContext
    {
        return $this->context;
    }

    public function addRecipientChannel(RecipientChannels $recipientChannels): self
    {
        $this->recipientChannels[] = $recipientChannels;

        return $this;
    }

    public function setRecipientChannels(array $recipientChannels): self
    {
        $this->recipientChannels = $recipientChannels;

        return $this;
    }

    public function send()
    {
        $channels = $this->getChannels();

        foreach ($channels as $channel => $recipients) {
            if (!empty($channels[$channel])) {
                $communication = $this->createCommunication($channel);
                foreach ($recipients as $recipient) {
                    $this->notifier->send($communication, $recipient);
                }
            }
        }
    }

    protected function getAllowedNotifications(): array
    {
        return $this->channels;
    }

    private function createCommunication($channel): Communication
    {
        $factory = $this->notificationFactories[$channel];

        return $factory->create($this->context, $channel);
    }

    private function getChannels(): array
    {
        $channels = [];
        foreach ($this->getAllowedNotifications() as $channel) {
            $channels[$channel] = [];
            foreach ($this->recipientChannels as $recipientChannel) {
                $channels[$channel] =
                    array_merge($channels[$channel], $recipientChannel->getForChannel($channel));
            }
        }

        return $channels;
    }
}
