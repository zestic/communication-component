<?php

declare(strict_types=1);

namespace Communication\Factory\Channel;

use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Channel\ChannelInterface;

interface ChannelFactoryInterface
{
    public function __invoke(ContainerInterface $container): ChannelInterface;
}
