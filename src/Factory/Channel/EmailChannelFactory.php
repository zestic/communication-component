<?php

declare(strict_types=1);

namespace Communication\Factory\Channel;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Channel\ChannelInterface;
use Symfony\Component\Notifier\Channel\EmailChannel;

final class EmailChannelFactory extends ChannelFactory
{
    public function __construct(
        protected string $channel,
    ) {
    }

    public function __invoke(ContainerInterface $container): ChannelInterface
    {
        $config = (new GatherConfigValues())($container, 'communication');
        $messageBus = null;
        if ($messageBusName = $this->getBus($config)) {
            $messageBus = $container->get($messageBusName);
            if (!$messageBus instanceof \Symfony\Component\Messenger\MessageBusInterface) {
                throw new \RuntimeException('Expected MessageBusInterface from container');
            }
        }

        $channelConfig = (new GatherConfigValues())($container, $this->channel);

        if (!isset($channelConfig['transport']) || !is_string($channelConfig['transport'])) {
            throw new \RuntimeException('Transport configuration is required and must be a string');
        }

        $transport = $container->get($channelConfig['transport']);
        if (!$transport instanceof \Symfony\Component\Mailer\Transport\TransportInterface) {
            throw new \RuntimeException('Expected TransportInterface from container');
        }

        $from = $channelConfig['from'] ?? null;
        $envelope = null;

        return new EmailChannel($transport, $messageBus, $from, $envelope);
    }
}
