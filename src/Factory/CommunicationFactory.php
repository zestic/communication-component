<?php

declare(strict_types=1);

namespace Communication\Factory;

use Communication\Communication;
use Communication\Interactor\SendCommunication;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

class CommunicationFactory implements AbstractFactoryInterface
{
    use CommunicationFactoryTrait;

    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        return (is_a($requestedName, Communication::class, true));
    }

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): mixed
    {
        $config = $container->get('config')['communication'];
        $context = $this->getContext($container, $config['context']);

        return new $requestedName(
            $context,
            $container->get(SendCommunication::class),
        );
    }
}
