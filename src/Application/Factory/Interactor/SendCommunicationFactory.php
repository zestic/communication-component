<?php

declare(strict_types=1);

namespace Communication\Application\Factory\Interactor;

use Communication\Context\CommunicationContext;
use Communication\Definition\Repository\CommunicationDefinitionRepositoryInterface;
use Communication\Factory\CommunicationFactory;
use Communication\Interactor\SendCommunication;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\NotifierInterface;

class SendCommunicationFactory
{
    public function __invoke(ContainerInterface $container): SendCommunication
    {
        $config = $container->get('config');

        if (!is_array($config) || !isset($config['communication']) || !is_array($config['communication'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication configuration');
        }

        $commConfig = $config['communication'];

        if (!isset($commConfig['channel']) || !is_array($commConfig['channel'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication.channel configuration');
        }

        if (!isset($commConfig['context']) || !is_array($commConfig['context'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication.context configuration');
        }

        $definitionRepository = $container->get(CommunicationDefinitionRepositoryInterface::class);
        $notificationFactories = $this->getNotificationFactories($container, $commConfig['channel']);
        $notifier = $container->get(NotifierInterface::class);
        $communicationFactory = $container->get(CommunicationFactory::class);

        return new SendCommunication($definitionRepository, $notificationFactories, $notifier, $communicationFactory);
    }

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
