<?php

declare(strict_types=1);

namespace Communication\Definition;

interface MobileChannelDefinitionInterface extends ChannelDefinition
{
    public function getPriority(): int;

    public function requiresAuth(): bool;
}
