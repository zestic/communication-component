<?php

declare(strict_types=1);

namespace Communication\Factory;

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
        $messageListener = new MessageListener(null, new BodyRenderer($twig));

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($messageListener);
        // add other subscribers and listeners

        return $dispatcher;
    }
}
