<?php

declare(strict_types=1);

namespace Communication\Application\Factory;

use Communication\Locator\CommunicationBusLocator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Netglue\PsrContainer\Messenger\Container\Util;
use Psr\Container\ContainerInterface;

final class EmailBusLocatorFactory implements FactoryInterface
{
    public function __construct(
        private string $busIdentifier,
    ) {
    }

    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): mixed
    {
        if ($this->busIdentifier === '') {
            throw new \InvalidArgumentException('Bus identifier cannot be empty');
        }

        $options = Util::messageBusOptions($container, $this->busIdentifier);

        return new CommunicationBusLocator($options->handlers(), $container);
    }
}
