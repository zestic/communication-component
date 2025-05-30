<?php

declare(strict_types=1);

namespace Communication\Application\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Messenger\MessageHandler;

final class MessageHandlerFactory implements FactoryInterface
{
    private string $transport;

    public function __construct(string $transport)
    {
        $this->transport = $transport;
    }

    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): mixed
    {
        $transport = $container->get($this->transport);
        if (!$transport instanceof \Symfony\Component\Mailer\Transport\TransportInterface) {
            throw new \RuntimeException('Expected TransportInterface from container');
        }

        return new MessageHandler($transport);
    }
}
