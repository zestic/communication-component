<?php

declare(strict_types=1);

namespace Communication\Application\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Twig\Environment;

class BodyRendererFactory
{
    public function __invoke(ContainerInterface $container): BodyRenderer
    {
        $twig = $container->get(Environment::class);
        if (!$twig instanceof Environment) {
            throw new \RuntimeException('Expected Twig\Environment from container');
        }

        return new BodyRenderer($twig);
    }
}
