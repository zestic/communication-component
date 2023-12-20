<?php

declare(strict_types=1);

namespace Communication\Factory\Transport\Email;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PostmarkFactory
{
    public function __construct(
        private string $id,
    ) {
    }

    public function __invoke(ContainerInterface $container): TransportInterface
    {
        $config = (new GatherConfigValues)($container, $this->id);
        $dispatcher = $container->get(EventDispatcherInterface::class);
        $logger = isset($config['logger']) ? $container->get($config['logger']) : null;
        $host = $config['host'] ?? 'default';

        $transportFactory = new PostmarkTransportFactory($dispatcher, HttpClient::create(), $logger);
        $dns = new Dsn("postmark+{$config['scheme']}", $host, $config['username']);

        return $transportFactory->create($dns);
    }
}
