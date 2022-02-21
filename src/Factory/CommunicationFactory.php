<?php
declare(strict_types=1);

namespace Communication\Factory;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Communication\Context\CommunicationContext;
use Communication\Communication;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\NotifierInterface;

class CommunicationFactory implements AbstractFactoryInterface
{
    use CommunicationFactoryTrait;

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return (is_a($requestedName, Communication::class, true));
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['communication'];
        $notificationFactories = $this->getNotificationFactories($container, $config['channel']);
        $context = $this->getContext($container, $config['context']);
        $notifier = $container->get(NotifierInterface::class);

        return new $requestedName($context, $notificationFactories, $notifier);
    }
}
