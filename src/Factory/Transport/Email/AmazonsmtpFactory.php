<?php
declare(strict_types=1);

namespace Communication\Factory\Transport\Email;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesSmtpTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class AmazonsmtpFactory
{
    /** @var string */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function __invoke(ContainerInterface $container): SesSmtpTransport
    {
        $config = (new GatherConfigValues)($container, $this->id);
        $dispatcher = $container->get(EventDispatcherInterface::class);
        $logger = null;

        return new SesSmtpTransport($config['username'], $config['password'], $config['region'], $dispatcher, $logger);
    }
}
