<?php

declare(strict_types=1);

namespace Communication\Application\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\EventListener\MessageListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

final class EventDispatcherFactory
{
    public function __invoke(ContainerInterface $container): EventDispatcherInterface
    {
        $twig = $container->get(Environment::class);
        if (!$twig instanceof Environment) {
            throw new \RuntimeException('Expected Twig\Environment from container');
        }

        $messageListener = new MessageListener(null, new BodyRenderer($twig));

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($messageListener);
        // add other subscribers and listeners

        return $dispatcher;
    }
}
