<?php

declare(strict_types=1);

namespace Tests\Unit\Definition\Repository\Factory;

use Communication\Definition\Repository\CommunicationDefinitionRepositoryInterface;
use Communication\Definition\Repository\Factory\CommunicationDefinitionRepositoryFactory;
use Communication\Definition\Repository\MySqlCommunicationDefinitionRepository;
use Communication\Definition\Repository\PostgresCommunicationDefinitionRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

class CommunicationDefinitionRepositoryFactoryTest extends TestCase
{
    private ContainerInterface $container;

    private CommunicationDefinitionRepositoryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new CommunicationDefinitionRepositoryFactory();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testResolvesToMysqlRepository(): void
    {
        // Mock a MySQL PDO connection
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('mysql');

        $this->container->method('get')
            ->with(PDO::class)
            ->willReturn($pdo);

        $repository = ($this->factory)($this->container);

        $this->assertInstanceOf(MySqlCommunicationDefinitionRepository::class, $repository);
        $this->assertInstanceOf(CommunicationDefinitionRepositoryInterface::class, $repository);
    }

    public function testResolvesToPostgresRepository(): void
    {
        // Mock a PostgreSQL PDO connection
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('pgsql');

        $this->container->method('get')
            ->with(PDO::class)
            ->willReturn($pdo);

        $repository = ($this->factory)($this->container);

        $this->assertInstanceOf(PostgresCommunicationDefinitionRepository::class, $repository);
        $this->assertInstanceOf(CommunicationDefinitionRepositoryInterface::class, $repository);
    }

    public function testHandlesNormalizedDriverNames(): void
    {
        // Test uppercase driver names
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('PGSQL');

        $this->container->method('get')
            ->with(PDO::class)
            ->willReturn($pdo);

        $repository = ($this->factory)($this->container);

        $this->assertInstanceOf(PostgresCommunicationDefinitionRepository::class, $repository);
    }

    public function testThrowsForUnsupportedDriver(): void
    {
        // Mock an unsupported database driver
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('sqlite');

        $this->container->method('get')
            ->with(PDO::class)
            ->willReturn($pdo);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unsupported database driver/');

        ($this->factory)($this->container);
    }

    public function testThrowsWhenPdoNotFound(): void
    {
        $this->container->method('get')
            ->with(PDO::class)
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/PDO instance not found/');

        ($this->factory)($this->container);
    }

    /**
     * @dataProvider driverNormalizationProvider
     */
    public function testDriverNormalization(string $driverName, string $expectedClass): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn($driverName);

        $this->container->method('get')
            ->with(PDO::class)
            ->willReturn($pdo);

        $repository = ($this->factory)($this->container);

        $this->assertInstanceOf($expectedClass, $repository);
    }

    public static function driverNormalizationProvider(): array
    {
        return [
            'mysql lowercase' => ['mysql', MySqlCommunicationDefinitionRepository::class],
            'pdo_mysql' => ['pdo_mysql', MySqlCommunicationDefinitionRepository::class],
            'MySQL uppercase' => ['MySQL', MySqlCommunicationDefinitionRepository::class],
            'pgsql lowercase' => ['pgsql', PostgresCommunicationDefinitionRepository::class],
            'pdo_pgsql' => ['pdo_pgsql', PostgresCommunicationDefinitionRepository::class],
            'postgres' => ['postgres', PostgresCommunicationDefinitionRepository::class],
            'postgresql' => ['postgresql', PostgresCommunicationDefinitionRepository::class],
            'PGSQL uppercase' => ['PGSQL', PostgresCommunicationDefinitionRepository::class],
        ];
    }
}
