<?php
declare(strict_types=1);

namespace Communication\Factory;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Communication\Context\CommunicationContext;
use Communication\Communication;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\NotifierInterface;

class NotificationFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return (is_a($requestedName, Communication::class, true));
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['notification'];

        $routes = $this->getRoutes($config);
        $channels = $this->getChannels($requestedName, $config);
        $communicationFactories = $this->getCommunicationFactories($container, $config['channel']);
        $context = $this->getContext($container, $config['context']);
        $notifier = $container->get(NotifierInterface::class);

        return new $requestedName($notifier, $context, $channels, $communicationFactories);
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

    protected function getCommunicationFactories(ContainerInterface $container, array $config): array
    {
        $factories = [];
        foreach ($config as $channel => $settings) {
            $factories[$channel] = $container->get($settings['communication_factory']);
        }

        return $factories;
    }

    protected function getContext(ContainerInterface $container, array $config): CommunicationContext
    {
        $meta = [];
        foreach ($config as $channel => $context) {
            $factory = $context['factory'];
            $meta[$channel] = (new $factory())->create($container, $context['data']);
        }

        return new CommunicationContext([], $meta);
    }

    protected function getRoutes(array $config): array
    {
        $routes = [];
        return $routes;
    }
}
