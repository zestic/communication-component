<?php
declare(strict_types=1);

namespace Communication\Factory\Channel;

use ConfigValue\GatherConfigValues;
use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Channel\ChannelInterface;
use Symfony\Component\Notifier\Channel\EmailChannel;

final class EmailChannelFactory
{
    public function __construct(
        private string $channel,
    ) {
    }

    public function __invoke(ContainerInterface $container): ChannelInterface
    {
        $channelConfig = (new GatherConfigValues)($container, $this->channel);
        $transport = $container->get($channelConfig['transport']);
        $messageBusName = $channelConfig['message_bus'] ?? 'messenger.bus.email';
        $messageBus = $container->get($messageBusName);
        $from = $channelConfig['from'] ?? null;
        $envelope = null;

        return new EmailChannel($transport, $messageBus, $from, $envelope);
    }
}
