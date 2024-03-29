<?php

declare(strict_types=1);

namespace Communication\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Twig\Environment;

class BodyRendererFactory
{
    public function __invoke(ContainerInterface $container): BodyRenderer
    {
        $twig = $container->get(Environment::class);

        return new BodyRenderer($twig);
    }
}
