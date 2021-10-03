<?php
declare(strict_types=1);

namespace Communication\Factory\Transport\Email;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class SmtpFactory
{
    /** @var string */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function __invoke(ContainerInterface $container): EsmtpTransport
    {
        $config = (new GatherConfigValues)($container, $this->id);
        $dispatcher = $container->get(EventDispatcherInterface::class);
        $logger = $config['logger'] ?? null;

        $transport = new EsmtpTransport($config['uri'], (int) $config['port'], false, $dispatcher, $logger);
        $transport
            ->setPassword($config['password'])
            ->setUsername($config['username']);

        return $transport;
    }
}
