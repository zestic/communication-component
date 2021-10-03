<?php
declare(strict_types=1);

namespace Communication\Factory\Channel;

abstract class ChannelFactory implements ChannelFactoryInterface
{
    protected function getBus(array $config): ?string
    {
      return null;
    }

    protected function getRoutes(array $config): array
    {
        $routes = [];
        foreach ($config['routes'] as $name => $routeConfig) {
            if (is_array($routeConfig)) {
                // need to get array key for type
                $type = '';
                $routes[$name]['pipe'][$type] = $routeConfig;
            } else {
                $routes[$name]['pipe'][$routeConfig] = "communication.{$routeConfig}.{$name}";
            }
        }

        return $routes;
    }
}
