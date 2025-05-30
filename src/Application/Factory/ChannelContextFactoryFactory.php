<?php

declare(strict_types=1);

namespace Communication\Application\Factory;

use Communication\Factory\Context\ChannelContextFactory;
use Psr\Container\ContainerInterface;

final class ChannelContextFactoryFactory
{
    public function __invoke(ContainerInterface $container): ChannelContextFactory
    {
        $config = $container->get('config')['communication'];

        if (!isset($config['channelContexts']) || !is_array($config['channelContexts'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication.channelContexts configuration');
        }

        return new ChannelContextFactory($config['channelContexts']);
    }
}
