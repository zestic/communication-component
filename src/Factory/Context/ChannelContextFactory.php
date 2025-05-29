<?php

declare(strict_types=1);

namespace Communication\Factory\Context;

use Communication\Context\CommunicationContextInterface;

class ChannelContextFactory
{
    public function __construct(
        private array $channelContexts,
    ) {
    }

    public function create(string $channel): CommunicationContextInterface
    {
        if (!isset($this->channelContexts[$channel])) {
            throw new \RuntimeException("Unknown channel: $channel");
        }
        $context = $this->channelContexts[$channel];

        return new $context();
    }
}
