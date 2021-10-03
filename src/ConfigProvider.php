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
                'messenger.bus.email'                      => new MessageBusStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.bus.email.sender-middleware'    => new MessageSenderMiddlewareStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.bus.email.handler-middleware'   => new MessageHandlerMiddlewareStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.bus.email.bus-stamp-middleware' => new BusNameStampMiddlewareStaticFactory(
                    'messenger.bus.email'
                ),
                'messenger.transport.email'                => [TransportFactory::class, 'messenger.transport.email'],
                'messenger.handler.email'                  => new MessageHandlerFactory(
                    'communication.transport.email'
                ),
                'communication.channel.email'              => new EmailChannelFactory('communication.channel.email'),
                'communication.transport.email'            => new CommunicationTransportFactory(
                    'communication.transport.email'
                ),
                EmailBusLocator::class                     =>
                    new EmailBusLocatorFactory(
                        'messenger.bus.email'
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
                SendEmailMessage::class => 'messenger.transport.email',
            ],
            'buses'      => [
                'messenger.bus.email' => [
                    'allows_zero_handlers' => true,
                    'handler_locator'      => EmailBusLocator::class,
                    'handlers'             => [
                        SendEmailMessage::class => ['messenger.handler.email'],
                    ],
                    'middleware'           => [
                        'messenger.bus.email.bus-stamp-middleware',
                        'messenger.bus.email.sender-middleware',
                        'messenger.bus.email.handler-middleware',
                    ],
                    'routes'               => [
                        '*' => ['messenger.transport.email'],
                    ],
                ],
            ],
            'transports' => $this->getMessengerTransports(),
        ];
    }

    private function getMessengerTransports(): array
    {
        return [
            'messenger.transport.email' => [
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
                    'transport' => 'communication.transport.email',
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
