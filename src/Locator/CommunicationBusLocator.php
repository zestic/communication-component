<?php
declare(strict_types=1);

namespace Communication\Locator;

use Netglue\PsrContainer\Messenger\Exception\ConfigurationError;
use Netglue\PsrContainer\Messenger\HandlerLocator\OneToOneFqcnContainerHandlerLocator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;

class CommunicationBusLocator implements HandlersLocatorInterface
{
    /** @param iterable<string, list<string>|mixed> $handlers */
    public function __construct(
        private readonly iterable $handlers,
        private readonly ContainerInterface $container,
    ) {
    }

    /** @inheritDoc */
    public function getHandlers(Envelope $envelope): iterable
    {
        $message = $envelope->getMessage();
        $type = $message::class;
        foreach ($this->handlers as $messageName => $handlers) {
            if (! is_array($handlers)) {
                throw new ConfigurationError(
                    'Expected an array of handler identifiers to retrieve from the container',
                );
            }

            if ($messageName !== $type) {
                continue;
            }

            /** @psalm-var mixed $handlerName */
            foreach ($handlers as $handlerName) {
                assert(is_string($handlerName));
                $singleLocator = new OneToOneFqcnContainerHandlerLocator(
                    [$messageName => $handlerName],
                    $this->container,
                );

                yield from $singleLocator->getHandlers($envelope);
            }
        }
    }
}
