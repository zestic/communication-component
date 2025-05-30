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
        $config = $container->get('config');
        if (!is_array($config) || !isset($config['communication']) || !is_array($config['communication'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication configuration');
        }

        $commConfig = $config['communication'];
        if (!isset($commConfig['context']) || !is_array($commConfig['context'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication.context configuration');
        }
        $context = $this->getContext($container, $commConfig['context']);

        return new $requestedName(
            $context,
            $container->get(SendCommunication::class),
        );
    }
}
