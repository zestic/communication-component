<?php

declare(strict_types=1);

namespace Communication\Definition\Repository\Factory;

use Communication\Definition\Repository\CommunicationDefinitionRepositoryInterface;
use Communication\Definition\Repository\MySqlCommunicationDefinitionRepository;
use Communication\Definition\Repository\PostgresCommunicationDefinitionRepository;
use PDO;
use Psr\Container\ContainerInterface;
use RuntimeException;

class CommunicationDefinitionRepositoryFactory
{
    public function __invoke(ContainerInterface $container): CommunicationDefinitionRepositoryInterface
    {
        // Retrieve the PDO connection from the container
        $pdo = $container->get(PDO::class);

        if (!$pdo instanceof PDO) {
            throw new RuntimeException(
                'PDO instance not found in container under key PDO::class'
            );
        }

        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if (!is_string($driver) || $driver === '') {
            throw new RuntimeException('Missing or invalid PDO driver attribute');
        }

        // Normalize driver name for comparison
        $normalizedDriver = strtolower($driver);

        // Resolve to the appropriate repository implementation
        return match($normalizedDriver) {
            'mysql', 'pdo_mysql' => new MySqlCommunicationDefinitionRepository($pdo),
            'pgsql', 'pdo_pgsql', 'postgres', 'postgresql' => new PostgresCommunicationDefinitionRepository($pdo),
            default => throw new RuntimeException(
                sprintf(
                    'Unsupported database driver "%s" for CommunicationDefinitionRepository. '
                    . 'Supported drivers: mysql, pgsql',
                    $driver
                )
            ),
        };
    }
}
