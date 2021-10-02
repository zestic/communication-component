<?php
declare(strict_types=1);

namespace Communication\Factory\Context;

use Psr\Container\ContainerInterface;

interface ContextFactoryInterface
{
    public function create(ContainerInterface $container, array $data);
}
