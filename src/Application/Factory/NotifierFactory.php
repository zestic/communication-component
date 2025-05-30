<?php

declare(strict_types=1);

namespace Communication\Application\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;

final class NotifierFactory
{
    public function __invoke(ContainerInterface $container): NotifierInterface
    {
        $channels = [];
        $config = $container->get('config');

        if (!is_array($config) || !isset($config['communication']['channel']) || !is_array($config['communication']['channel'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication.channel configuration');
        }

        $channelList = $config['communication']['channel'];
        foreach ($channelList as $channelName => $channelConfig) {
            if (!is_array($channelConfig)) {
                throw new \RuntimeException(sprintf('Invalid channel configuration for %s', $channelName));
            }

            $serviceId = $channelConfig['channel'] ?? "communication.channel.{$channelName}";
            $channels[$channelName] = $container->get($serviceId);
        }

        return new Notifier($channels);
    }
}
