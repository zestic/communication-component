<?php

declare(strict_types=1);

namespace Communication\Factory\Context;

use Communication\Context\CommunicationContextInterface;
use Psr\Container\ContainerInterface;

class ChannelContextFactory
{
    public function __construct(
        private array $factories,
    ) {
    }

    public function create(string $channel): CommunicationContextInterface
    {
        if (!isset($this->factories[$channel])) {
            throw new \RuntimeException("Unknown channel: $channel");
        }
        $factory = $this->factories[$channel];
        $factory = $this->container->get("communication.context.{$channel}");

        return $factory->create($this->container, []);
    }
}