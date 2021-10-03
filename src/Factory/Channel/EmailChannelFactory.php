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
        private string $channel,
    ) {
    }

    public function __invoke(ContainerInterface $container): ChannelInterface
    {
        $config = (new GatherConfigValues)($container, 'communication');
        $routes = $this->getRoutes($config);
        if ($bus = $this->getBus($config)) {
            $messageBusName = $channelConfig['message_bus'] ?? 'messenger.bus.email';
            $messageBus = $container->get($messageBusName);
        } else {
            $messageBus = null;
        }
        // figure out the route here
        $channelConfig = (new GatherConfigValues)($container, $this->channel);

        $transport = $container->get($channelConfig['transport']);

        $from = $channelConfig['from'] ?? null;
        $envelope = null;

        return new EmailChannel($transport, $messageBus, $from, $envelope);
    }
}
