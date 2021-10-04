<?php
declare(strict_types=1);

namespace Communication;

use Mezzio\Twig\TwigEnvironmentFactory;
use Mezzio\Twig\TwigExtension;
use Mezzio\Twig\TwigExtensionFactory;
use Netglue\PsrContainer\Messenger\Container\MessageBusStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\BusNameStampMiddlewareStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\MessageHandlerMiddlewareStaticFactory;
use Netglue\PsrContainer\Messenger\Container\Middleware\MessageSenderMiddlewareStaticFactory;
use Netglue\PsrContainer\Messenger\Container\TransportFactory;
use Communication\Command\SendTestEmailCommand;
use Communication\Factory\Channel\EmailChannelFactory;
use Communication\Factory\Notification\EmailNotificationFactory;
use Communication\Factory\Context\EmailContextFactory;
use Communication\Factory\EventDispatcherFactory;
use Communication\Factory\NotifierFactory;
use Communication\Factory\Transport\CommunicationTransportFactory;
use Communication\Factory\EmailBusLocatorFactory;
use Communication\Factory\MessageHandlerFactory;
use Communication\Factory\CommunicationFactory;
use Communication\Locator\EmailBusLocator;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Notifier\Notifier;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'  => $this->getDependencies(),
            'laminas-cli'   => $this->getConsoleConfig(),
            'communication' => $this->getCommunicationConfig(),
            'symfony'       => [
                'messenger' => $this->getMessengerConfig(),
            ],
        ];
    }

    private function getConsoleConfig(): array
    {
        return [
            'commands' => [
                'communication:send-test-email' => SendTestEmailCommand::class,
            ],
        ];
    }

    private function getDependencies(): array
    {
        return [
            'abstract_factories' => [
                CommunicationFactory::class,
            ],
            'factories'          => [
                // bus config
                'communication.bus.email'                      => new MessageBusStaticFactory(
                    'communication.bus.email'
                ),
                'communication.bus.email.sender-middleware'    => new MessageSenderMiddlewareStaticFactory(
                    'communication.bus.email'
                ),
                'communication.bus.email.handler-middleware'   => new MessageHandlerMiddlewareStaticFactory(
                    'communication.bus.email'
                ),
                'communication.bus.email.bus-stamp-middleware' => new BusNameStampMiddlewareStaticFactory(
                    'communication.bus.email'
                ),
                'communication.bus.transport.email'                => [TransportFactory::class, 'communication.bus.transport.email'],
                'communication.bus.handler.email'                  => new MessageHandlerFactory(
                    'communication.channel.transport.email'
                ),
                // channel config
                'communication.channel.email'              => new EmailChannelFactory('communication.channel.email'),
                'communication.channel.transport.email'            => new CommunicationTransportFactory(
                    'communication.channel.transport.email'
                ),
                EmailBusLocator::class                     =>
                    new EmailBusLocatorFactory(
                        'communication.bus.email'
                    ),
                EventDispatcherInterface::class            => EventDispatcherFactory::class,
                TwigExtension::class                       => TwigExtensionFactory::class,
                Environment::class                         => TwigEnvironmentFactory::class,
                Notifier::class                            => NotifierFactory::class,
            ],
        ];
    }

    private function getMessengerConfig(): array
    {
        return [
            'routing'    => [
                SendEmailMessage::class => 'communication.bus.transport.email',
            ],
            'buses'      => [
                'communication.bus.email' => [
                    'allows_zero_handlers' => true,
                    'handler_locator'      => EmailBusLocator::class,
                    'handlers'             => [
                        SendEmailMessage::class => ['communication.bus.handler.email'],
                    ],
                    'middleware'           => [
                        'communication.bus.email.bus-stamp-middleware',
                        'communication.bus.email.sender-middleware',
                        'communication.bus.email.handler-middleware',
                    ],
                    'routes'               => [
                        '*' => ['communication.bus.transport.email'],
                    ],
                ],
            ],
            'transports' => $this->getMessengerTransports(),
        ];
    }

    private function getMessengerTransports(): array
    {
        return [
            'communication.bus.transport.email' => [
                'dsn'            => 'doctrine://dbal-default?queue_name=email',
                'serializer'     => PhpSerializer::class,
                'options'        => [
                ],
                'retry_strategy' => [
                    'max_retries' => 3,
                    'delay'       => 1000,
                    'multiplier'  => 2,
                    'max_delay'   => 0,
                ],
            ],
        ];
    }

    private function getCommunicationConfig(): array
    {
        return [
            'channel' => [
                'email' => [
                    'factory'   => EmailNotificationFactory::class,
                    'transport' => 'communication.channel.transport.email',
                ],
            ],
            'context' => [
                'email' => [
                    'factory' => EmailContextFactory::class,
                    'data'    => [
                    ],
                ],
            ],
        ];
    }
}
