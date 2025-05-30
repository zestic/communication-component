<?php

declare(strict_types=1);

namespace Communication\Factory\Legacy;

use Communication\Communication;
use Communication\Interactor\SendCommunication;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

class CommunicationFactory implements AbstractFactoryInterface
{
    use CommunicationFactoryTrait;

    public function canCreate(ContainerInterface $container, string $requestedName): bool
    {
        return (is_a($requestedName, Communication::class, true));
    }

    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): mixed
    {
        $config = $container->get('config')['communication'];
        if (!isset($config['context']) || !is_array($config['context'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication.context configuration');
        }
        $context = $this->getContext($container, $config['context']);

        return new $requestedName(
            $context,
            $container->get(SendCommunication::class),
        );
    }
}
