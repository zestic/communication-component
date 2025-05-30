<?php

declare(strict_types=1);

namespace Communication\Factory\Context;

use Communication\Context\CommunicationContextInterface;

class ChannelContextFactory
{
    /**
     * @param array<string, class-string<CommunicationContextInterface>> $channelContexts
     */
    public function __construct(
        private array $channelContexts,
    ) {
    }

    public function create(string $channel): CommunicationContextInterface
    {
        if (!isset($this->channelContexts[$channel])) {
            throw new \RuntimeException("Unknown channel: $channel");
        }
        $contextClass = $this->channelContexts[$channel];

        /** @var CommunicationContextInterface */
        return new $contextClass();
    }
}
