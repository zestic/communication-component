<?php

declare(strict_types=1);

namespace Communication\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Messenger\MessageHandler;

final class MessageHandlerFactory implements FactoryInterface
{
    /** @var string */
    private $transport;

    public function __construct(string $transport)
    {
        $this->transport = $transport;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): mixed
    {
        $transport = $container->get($this->transport);

        return new MessageHandler($transport);
    }
}
