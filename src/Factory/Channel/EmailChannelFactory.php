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
        $config = (new GatherConfigValues)($container, 'communication');
        if ($messageBusName = $this->getBus($config)) {
            $messageBus = $container->get($messageBusName);
        } else {
            $messageBus = null;
        }
        $channelConfig = (new GatherConfigValues)($container, $this->channel);
        $transport = $container->get($channelConfig['transport']);

        $from = $channelConfig['from'] ?? null;
        $envelope = null;

        return new EmailChannel($transport, $messageBus, $from, $envelope);
    }
}
