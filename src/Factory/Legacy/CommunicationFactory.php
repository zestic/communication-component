<?php

declare(strict_types=1);

namespace Communication\Factory\Legacy;

use Communication\Communication;
use Communication\Context\CommunicationContext;
use Communication\Context\CommunicationContextInterface;
use Communication\Interactor\SendCommunication;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

class CommunicationFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, string $requestedName): bool
    {
        return (is_a($requestedName, Communication::class, true));
    }

    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): mixed
    {
        $config = $container->get('config');
        if (!is_array($config) || !isset($config['communication']) || !is_array($config['communication'])) {
            throw new \RuntimeException('Invalid configuration: missing or invalid communication configuration');
        }

        $commConfig = $config['communication'];

        if (array_key_exists('context', $commConfig)) {
            throw new \RuntimeException(
                "Configuration key 'communication.context' is no longer supported in v2.\n"
                . "Replace with 'communication.channelContexts'. See docs/UPGRADE-v2.md for details."
            );
        }

        if (!isset($commConfig['channelContexts'])) {
            throw new \RuntimeException(
                "Missing required configuration key 'communication.channelContexts'.\n"
                . 'This is a v2-required configuration. See docs/UPGRADE-v2.md for migration steps.'
            );
        }

        if (!is_array($commConfig['channelContexts'])) {
            throw new \RuntimeException(
                "Invalid configuration key 'communication.channelContexts': expected an array of channel context classes."
            );
        }

        $context = new CommunicationContext($this->buildChannelContexts($commConfig['channelContexts']));

        return new $requestedName(
            $context,
            $container->get(SendCommunication::class),
        );
    }

    /**
     * @param array<string, mixed> $channelContexts
     * @return array<string, CommunicationContextInterface>
     */
    private function buildChannelContexts(array $channelContexts): array
    {
        $contexts = [];

        foreach ($channelContexts as $channel => $contextClass) {
            if (!is_string($contextClass) || !class_exists($contextClass)) {
                throw new \RuntimeException(
                    sprintf(
                        "Invalid communication.channelContexts entry for '%s': expected a valid class name.",
                        (string) $channel
                    )
                );
            }

            if (!is_subclass_of($contextClass, CommunicationContextInterface::class)) {
                throw new \RuntimeException(
                    sprintf(
                        "Invalid communication.channelContexts entry for '%s': class must implement %s.",
                        (string) $channel,
                        CommunicationContextInterface::class
                    )
                );
            }

            /** @var CommunicationContextInterface $context */
            $context = new $contextClass();
            $contexts[(string) $channel] = $context;
        }

        return $contexts;
    }
}
