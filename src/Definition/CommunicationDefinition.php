<?php

declare(strict_types=1);

namespace Communication\Definition;

class CommunicationDefinition
{
    /** @var ChannelDefinition[] */
    private array $channelDefinitions = [];

    public function __construct(
        private string $identifier,
        private string $name
    ) {}

    public function addChannelDefinition(ChannelDefinition $definition): self
    {
        $this->channelDefinitions[$definition->getChannel()] = $definition;

        return $this;
    }

    public function getChannelDefinition(string $channel): ?ChannelDefinition
    {
        return $this->channelDefinitions[$channel] ?? null;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /** @return ChannelDefinition[] */
    public function getChannelDefinitions(): array
    {
        return $this->channelDefinitions;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
