<?php
declare(strict_types=1);

namespace Communication\Factory\Channel;

abstract class ChannelFactory implements ChannelFactoryInterface
{
    protected function getBus(array $config): ?string
    {
        $channelParts = explode('.', $this->channel);
        $channelName = array_pop($channelParts);
        foreach ($config['routes'] as $name => $routeConfig) {
            if ($name != $channelName) {
                continue;
            }
            if (is_array($routeConfig)) {
                if ('bus' != array_key_first($routeConfig)) {
                    return null;
                }

                return $routeConfig['bus'];
            }
            if ('bus' != $routeConfig) {
                return null;
            }

            return "communication.{$routeConfig}.{$name}";
        }

        return null;
    }
}
