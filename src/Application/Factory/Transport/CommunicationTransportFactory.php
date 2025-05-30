<?php

declare(strict_types=1);

namespace Communication\Application\Factory\Transport;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;

final class CommunicationTransportFactory
{
    public function __construct(
        private string $config,
    ) {
    }

    public function __invoke(ContainerInterface $container)
    {
        $config = (new GatherConfigValues())($container, $this->config);
        $type = ucfirst(strtolower($config['type']));
        $transportFactory = "Communication\\Application\\Factory\\Transport\\Email\\{$type}Factory";

        return (new $transportFactory($this->config))($container);
    }
}
