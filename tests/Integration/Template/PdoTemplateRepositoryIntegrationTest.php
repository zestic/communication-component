<?php

declare(strict_types=1);

namespace Tests\Integration\Template;

use Communication\Template\PdoTemplateRepository;
use Communication\Template\Template;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Communication\Template\PdoTemplateRepository
 * @uses \Communication\Template\Template
 */
class PdoTemplateRepositoryIntegrationTest extends TestCase
{
    private PDO $pdo;

    private PdoTemplateRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            'pgsql:host=localhost;dbname=test',
            'test',
            'password1',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        // Drop table if exists and create new one
        $this->pdo->exec('DROP TABLE IF EXISTS communication_templates');
        $this->pdo->exec('
            CREATE TABLE communication_templates (
                id VARCHAR(26) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                channel VARCHAR(50) NOT NULL,
                subject VARCHAR(255),
                content TEXT NOT NULL,
                content_type VARCHAR(50) NOT NULL DEFAULT \'text/html\',
                metadata JSONB,
                created_at TIMESTAMP WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP WITH TIME ZONE NOT NULL,
                UNIQUE (name, channel)
            )
        ');

        $this->repository = new PdoTemplateRepository($this->pdo);
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
