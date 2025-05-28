<?php

declare(strict_types=1);

namespace Communication\Factory\Legacy;

use Communication\Context\CommunicationContext;
use Psr\Container\ContainerInterface;

trait CommunicationFactoryTrait
{
    protected function getChannels(string $requestedName, array $config): array
    {
        $channelNames = array_keys($config['channel']) ?? ['email'];
        if (isset($config[$requestedName])) {
            $channelNames = $config[$requestedName]['channel'] ?? $channelNames;
        }
        $channels = [];
        foreach ($channelNames as $channel) {
            $channels[$channel] = $channel . '/' . $config['channel'][$channel]['messenger'];
        }

        return $channels;
    }

    protected function getNotificationFactories(ContainerInterface $container, array $config): array
    {
        $factories = [];
        foreach ($config as $channel => $settings) {
            $factories[$channel] = $container->get($settings['factory']);
        }

        return $factories;
    }

    protected function getContext(ContainerInterface $container, array $config): CommunicationContext
    {
        $contexts = [];
        foreach ($config as $channel => $channelConfig) {
            $factory = $channelConfig['factory'];
            $contexts[$channel] = (new $factory())->create($container, $channelConfig);
        }

        return new CommunicationContext($contexts);
    }

    protected function getRoutes(array $config): array
    {
        $routes = [];
        foreach ($config['routes'] as $name => $routeConfig) {
            if (is_array($routeConfig)) {
                // need to get array key for type
                $type = '';
                $routes[$name]['pipe'][$type] = $routeConfig;
            } else {
                $routes[$name]['pipe'][$routeConfig] = "communication.{$routeConfig}.{$name}";
            }
        }

        return $routes;
    }
}
