<?php

declare(strict_types=1);

namespace Communication\Application\Factory;

use Communication\Factory\Context\ChannelContextFactory;
use Psr\Container\ContainerInterface;

final class ChannelContextFactoryFactory
{
    public function __invoke(ContainerInterface $container): ChannelContextFactory
    {
        $config = $container->get('config');
        if (!is_array($config) || !isset($config['communication']) || !is_array($config['communication'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication configuration');
        }

        $commConfig = $config['communication'];
        if (!isset($commConfig['channelContexts']) || !is_array($commConfig['channelContexts'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication.channelContexts configuration');
        }

        return new ChannelContextFactory($commConfig['channelContexts']);
    }
}
