<?php

declare(strict_types=1);

namespace Communication\Definition\Repository;

use Communication\Definition\CommunicationDefinition;

interface CommunicationDefinitionRepositoryInterface
{
    public function findByIdentifier(string $identifier): ?CommunicationDefinition;

    public function save(CommunicationDefinition $definition): void;
}
