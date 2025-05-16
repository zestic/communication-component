<?php

declare(strict_types=1);

namespace Communication;

use Communication\Context\CommunicationContext;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

abstract class Communication
{
    /** @var \Communication\Recipient[] */
    private array $channelRecipients = [];

    public function __construct(
        protected CommunicationContext $context,
        private readonly array $notificationFactories,
        private readonly NotifierInterface $notifier,
    ) {
        foreach ($this->getAllowedChannels() as $channel) {
            $this->channelRecipients[$channel] = [];
        }
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
            $this->addRecipientToChannels($recipient);
        }

        return $this;
    }

    public function createNotification($channel): Notification
    {
        $factory = $this->notificationFactories[$channel];
        $context = $this->context->getContext($channel);

        return $factory->create($context, $channel);
    }

    public function send()
    {
        $this->setTemplates();
        foreach ($this->getChannelRecipients() as $channel => $recipients) {
            if (!empty($recipients)) {
                foreach ($recipients as $index => $recipient) {
                    $notification = $this->createNotification($channel);
                    $this->notifier->send($notification, $recipient);
                    $this->removeRecipientAtIndex($channel, $index);
                }
            }
        }
    }

    public function setFrom(Recipient|Address|string $address)
    {
        $this->context->setFrom($address);
    }

    protected function getChannelRecipients(): array
    {
        return $this->channelRecipients;
    }

    abstract protected function getAllowedChannels(): array;

    abstract protected function getTemplates(): array;

    private function addRecipientToChannels(Recipient $recipient)
    {
        foreach ($recipient->getChannels() as $channel) {
            if (isset($this->channelRecipients[$channel])) {
                $this->channelRecipients[$channel][] = $recipient;
            }
        }
    }

    private function removeRecipientAtIndex(string $channel, $index)
    {
        unset($this->channelRecipients[$channel][$index]);
    }

    private function setTemplates()
    {
        foreach ($this->getTemplates() as $channel => $templates) {
            $context = $this->context->getContext($channel);
            foreach ($templates as $type => $template) {
                $setter = 'set' . ucfirst(strtolower($type)) . 'Template';
                $context->$setter($template);
            }
        }
    }
}
