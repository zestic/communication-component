<?php

declare(strict_types=1);

namespace Tests\Unit\Template;

use Communication\Template\PdoTemplateRepository;
use Communication\Template\Template;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PDO;
use PDOStatement;

/**
 * @covers \Communication\Template\PdoTemplateRepository
 * @uses \Communication\Template\Template
 */
class PdoTemplateRepositoryTest extends MockeryTestCase
{
    private PDO|Mockery\MockInterface $pdo;

    private PdoTemplateRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = Mockery::mock(PDO::class);
        $this->pdo->shouldReceive('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->andReturn('mysql');

        $this->pdo->shouldReceive('setAttribute')
            ->with(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC)
            ->once();

        $this->repository = new PdoTemplateRepository($this->pdo);
    }

    /**
     * @covers \Communication\Template\PdoTemplateRepository::findByNameAndChannel
     * @covers \Communication\Template\PdoTemplateRepository::hydrate
     * @covers \Communication\Template\PdoTemplateRepository::decodeMetadata
     */
    public function testFindByNameAndChannel(): void
    {
        $stmt = Mockery::mock(PDOStatement::class);
        $stmt->shouldReceive('execute')
            ->once()
            ->with(['name' => 'welcome', 'channel' => 'email'])
            ->andReturnTrue();

        $stmt->shouldReceive('fetch')
            ->once()
            ->andReturn([
                'id' => 'template123',
                'name' => 'welcome',
                'channel' => 'email',
                'subject' => 'Welcome!',
                'content' => 'Hello {{ name }}',
                'content_type' => 'text/html',
                'metadata' => json_encode(['category' => 'onboarding']),
                'created_at' => '2025-01-01 12:00:00',
                'updated_at' => '2025-01-01 12:00:00',
            ]);

        $this->pdo->shouldReceive('prepare')
            ->once()
            ->andReturn($stmt);

        $template = $this->repository->findByNameAndChannel('welcome', 'email');

        $this->assertInstanceOf(Template::class, $template);
        $this->assertSame('template123', $template->getId());
        $this->assertSame('welcome', $template->getName());
        $this->assertSame('email', $template->getChannel());
        $this->assertSame('Welcome!', $template->getSubject());
        $this->assertSame('Hello {{ name }}', $template->getContent());
        $this->assertSame(['category' => 'onboarding'], $template->getMetadata());
    }

    /**
     * @covers \Communication\Template\PdoTemplateRepository::findByName
     * @covers \Communication\Template\PdoTemplateRepository::hydrate
     * @covers \Communication\Template\PdoTemplateRepository::decodeMetadata
     */
    public function testFindByName(): void
    {
        $stmt = Mockery::mock(PDOStatement::class);
        $stmt->shouldReceive('execute')
            ->once()
            ->with(['name' => 'welcome.html.twig'])
            ->andReturnTrue();

        $stmt->shouldReceive('fetch')
            ->once()
            ->andReturn([
                'id' => 'template123',
                'name' => 'welcome.html.twig',
                'channel' => 'email',
                'subject' => 'Welcome!',
                'content' => 'Hello {{ name }}',
                'content_type' => 'text/html',
                'metadata' => json_encode(['category' => 'onboarding']),
                'created_at' => '2025-01-01 12:00:00',
                'updated_at' => '2025-01-01 12:00:00',
            ]);

        $this->pdo->shouldReceive('prepare')
            ->once()
            ->andReturn($stmt);

        $template = $this->repository->findByName('welcome.html.twig');

        $this->assertInstanceOf(Template::class, $template);
        $this->assertSame('template123', $template->getId());
        $this->assertSame('welcome.html.twig', $template->getName());
        $this->assertSame('email', $template->getChannel());
        $this->assertSame('Welcome!', $template->getSubject());
        $this->assertSame('Hello {{ name }}', $template->getContent());
        $this->assertSame(['category' => 'onboarding'], $template->getMetadata());
    }

    /**
     * @covers \Communication\Template\PdoTemplateRepository::save
     * @covers \Communication\Template\PdoTemplateRepository::encodeMetadata
     * @covers \Communication\Template\PdoTemplateRepository::formatDateTime
     */
    public function testSave(): void
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

        // Mock update statement
        $updateStmt = Mockery::mock(PDOStatement::class);
        $updateStmt->shouldReceive('bindValue')
            ->times(9)
            ->andReturnTrue();
        $updateStmt->shouldReceive('execute')
            ->once()
            ->andReturnTrue();
        $updateStmt->shouldReceive('rowCount')
            ->once()
            ->andReturn(0);

        // Mock insert statement
        $insertStmt = Mockery::mock(PDOStatement::class);
        $insertStmt->shouldReceive('bindValue')
            ->times(9)
            ->andReturnTrue();
        $insertStmt->shouldReceive('execute')
            ->once()
            ->andReturnTrue();

        $this->pdo->shouldReceive('prepare')
            ->twice()
            ->andReturn($updateStmt, $insertStmt);

        $this->repository->save($template);
    }
}
