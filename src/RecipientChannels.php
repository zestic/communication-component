<?php

declare(strict_types=1);

namespace Communication;

final class RecipientChannels
{
    private array $channelRecipients = [];

    /**
     * @param Recipient|Recipient[] $recipients
     */
    public function addRecipientsToChannel(string $channel, $recipients): self
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        if (!isset($this->channelRecipients[$channel])) {
            $this->channelRecipients[$channel] = [];
        }

        $this->channelRecipients[$channel] = array_merge($this->channelRecipients[$channel], $recipients);

        return $this;
    }

    /**
     * @return Recipient[]
     */
    public function getForChannel(string $channel): array
    {
        return $this->channelRecipients[$channel] ?? [];
    }
}
