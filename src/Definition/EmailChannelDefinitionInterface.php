<?php

declare(strict_types=1);

namespace Communication\Definition;

interface EmailChannelDefinitionInterface extends ChannelDefinition
{
    public function getFromAddress(): ?string;

    public function getReplyTo(): ?string;
}
