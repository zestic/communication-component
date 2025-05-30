<?php

declare(strict_types=1);

namespace Tests\Integration\Template;

use Communication\Template\PdoTemplateRepository;
use Communication\Template\Template;
use DateTimeImmutable;
use PDO;
use Tests\Integration\IntegrationTestCase;

/**
 * @covers \Communication\Template\PdoTemplateRepository
 * @uses \Communication\Template\Template
 */
class PdoTemplateRepositoryIntegrationTest extends IntegrationTestCase
{
    private PDO $pdo;

    private PdoTemplateRepository $repository;

    protected function setUp(): void
    {
        // Get database connection parameters from environment variables
        $host = getenv('POSTGRES_TEST_HOST');
        $port = getenv('POSTGRES_TEST_PORT');
        $dbname = getenv('POSTGRES_TEST_DB');
        $user = getenv('POSTGRES_TEST_USER');
        $password = getenv('POSTGRES_TEST_PASSWORD');
        $schema = getenv('POSTGRES_TEST_SCHEMA');

        // For backward compatibility with existing tests
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};options='--search_path={$schema}'";

        $this->pdo = new PDO(
            $dsn,
            $user ?: null,
            $password ?: null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        // Ensure the schema exists
        $this->pdo->exec("CREATE SCHEMA IF NOT EXISTS {$schema}");

        // Drop table if exists to ensure a clean state
        $this->pdo->exec("DROP TABLE IF EXISTS {$schema}.communication_templates");

        // Create table manually since Phinx migrations are not working properly
        $this->createTable($schema ?: 'communication_component');

        $this->repository = new PdoTemplateRepository($this->pdo);
    }

    private function createTable(string $schema): void
    {
        // Create communication_templates table
        $this->pdo->exec("
            CREATE TABLE {$schema}.communication_templates (
                id VARCHAR(26) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                channel VARCHAR(50) NOT NULL,
                subject VARCHAR(255),
                content TEXT NOT NULL,
                content_type VARCHAR(50) NOT NULL DEFAULT 'text/html',
                metadata JSONB,
                created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
                UNIQUE (name, channel)
            )
        ");
    }

    /**
     * @covers \Communication\Template\PdoTemplateRepository::save
     * @covers \Communication\Template\PdoTemplateRepository::findByNameAndChannel
     */
    public function testSaveAndRetrieveTemplate(): void
    {
        $template = new Template(
            'template123',
            'welcome',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            ['category' => 'onboarding'],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->save($template);

        $retrieved = $this->repository->findById('template123');
        $this->assertNotNull($retrieved);
        $this->assertSame('template123', $retrieved->getId());
        $this->assertSame('welcome', $retrieved->getName());
        $this->assertSame('email', $retrieved->getChannel());
        $this->assertSame('Hello {{ name }}', $retrieved->getContent());
        $this->assertSame(['category' => 'onboarding'], $retrieved->getMetadata());
    }

    /**
     * @covers \Communication\Template\PdoTemplateRepository::save
     * @covers \Communication\Template\PdoTemplateRepository::findByNameAndChannel
     */
    public function testUpdateExistingTemplate(): void
    {
        $template = new Template(
            'template123',
            'welcome',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            ['category' => 'onboarding'],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->save($template);

        $updatedTemplate = new Template(
            'template123',
            'welcome',
            'email',
            'Updated content',
            'text/html',
            'Updated subject',
            ['category' => 'updated'],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-02 12:00:00')
        );

        $this->repository->save($updatedTemplate);

        $retrieved = $this->repository->findById('template123');
        $this->assertNotNull($retrieved);
        $this->assertSame('Updated content', $retrieved->getContent());
        $this->assertSame('Updated subject', $retrieved->getSubject());
        $this->assertSame(['category' => 'updated'], $retrieved->getMetadata());
    }

    /**
     * @covers \Communication\Template\PdoTemplateRepository::findByNameAndChannel
     */
    public function testFindByNameAndChannel(): void
    {
        $template = new Template(
            'template123',
            'welcome',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            ['category' => 'onboarding'],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->save($template);

        $retrieved = $this->repository->findByNameAndChannel('welcome', 'email');
        $this->assertNotNull($retrieved);
        $this->assertSame('template123', $retrieved->getId());

        $nonexistent = $this->repository->findByNameAndChannel('nonexistent', 'email');
        $this->assertNull($nonexistent);
    }

    /**
     * @covers \Communication\Template\PdoTemplateRepository::delete
     * @covers \Communication\Template\PdoTemplateRepository::findByNameAndChannel
     */
    public function testDelete(): void
    {
        $template = new Template(
            'template123',
            'welcome',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            ['category' => 'onboarding'],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->save($template);
        $this->assertNotNull($this->repository->findById('template123'));

        $this->repository->delete('template123');
        $this->assertNull($this->repository->findById('template123'));
    }
}
