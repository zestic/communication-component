<?php
declare(strict_types=1);

namespace Communication\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Netglue\PsrContainer\Messenger\Container\MessageBusOptionsRetrievalBehaviour;
use Netglue\PsrContainer\Messenger\HandlerLocator\OneToManyFqcnContainerHandlerLocator;
use Psr\Container\ContainerInterface;

final class EmailBusLocatorFactory implements FactoryInterface
{
    use MessageBusOptionsRetrievalBehaviour;

    /** @var string */
    private $busIdentifier;

    public function __construct(string $busIdentifier)
    {
        $this->busIdentifier = $busIdentifier;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $this->options($container, $this->busIdentifier);

        return new OneToManyFqcnContainerHandlerLocator($options->handlers(), $container);
    }
}
