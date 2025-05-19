<?php

declare(strict_types=1);

namespace Tests\Integration\Communication\Definition\Repository;

use Communication\Definition\CommunicationDefinition;
use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\MobileChannelDefinition;
use Communication\Definition\Repository\PostgresCommunicationDefinitionRepository;
use PDO;
use PHPUnit\Framework\TestCase;

class PostgresCommunicationDefinitionRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PostgresCommunicationDefinitionRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            getenv('POSTGRES_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=communication_test',
            getenv('POSTGRES_USER') ?: 'postgres',
            getenv('POSTGRES_PASSWORD') ?: 'postgres'
        );
        
        $this->pdo->exec(file_get_contents(__DIR__ . '/../../../../migrations/V1__create_communication_definitions.sql'));
        $this->repository = new PostgresCommunicationDefinitionRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        $this->pdo->exec('DROP TABLE IF EXISTS channel_definitions');
        $this->pdo->exec('DROP TABLE IF EXISTS communication_definitions');
    }

    public function testSaveAndRetrieve(): void
    {
        // Create a test definition
        $definition = new CommunicationDefinition('test.notification', 'Test Notification');

        $emailDef = new EmailChannelDefinition(
            'email-template',
            ['type' => 'object', 'required' => ['body']],
            ['type' => 'object', 'required' => ['subject']],
            'from@example.com',
            'reply@example.com'
        );

        $mobileDef = new MobileChannelDefinition(
            'mobile-template',
            ['type' => 'object', 'required' => ['message']],
            ['type' => 'object', 'required' => ['title']],
            2,
            true
        );

        $definition->addChannelDefinition($emailDef);
        $definition->addChannelDefinition($mobileDef);

        // Save the definition
        $this->repository->save($definition);

        // Retrieve and verify
        $retrieved = $this->repository->findByIdentifier('test.notification');
        
        $this->assertNotNull($retrieved);
        $this->assertEquals('test.notification', $retrieved->getIdentifier());
        $this->assertEquals('Test Notification', $retrieved->getName());
        
        $retrievedEmailDef = $retrieved->getChannelDefinition('email');
        $this->assertNotNull($retrievedEmailDef);
        $this->assertInstanceOf(EmailChannelDefinition::class, $retrievedEmailDef);
        $this->assertEquals('email-template', $retrievedEmailDef->getTemplate());
        $this->assertEquals('from@example.com', $retrievedEmailDef->getFromAddress());
        $this->assertEquals('reply@example.com', $retrievedEmailDef->getReplyTo());
        
        $retrievedMobileDef = $retrieved->getChannelDefinition('mobile');
        $this->assertNotNull($retrievedMobileDef);
        $this->assertInstanceOf(MobileChannelDefinition::class, $retrievedMobileDef);
        $this->assertEquals('mobile-template', $retrievedMobileDef->getTemplate());
        $this->assertEquals(2, $retrievedMobileDef->getPriority());
        $this->assertTrue($retrievedMobileDef->requiresAuth());
    }

    public function testNonExistentDefinition(): void
    {
        $retrieved = $this->repository->findByIdentifier('non.existent');
        $this->assertNull($retrieved);
    }

    public function testUpdateExistingDefinition(): void
    {
        // Create initial definition
        $definition = new CommunicationDefinition('test.notification', 'Test Notification');
        $emailDef = new EmailChannelDefinition(
            'email-template',
            ['type' => 'object', 'required' => ['body']],
            ['type' => 'object', 'required' => ['subject']],
            'from@example.com',
            'reply@example.com'
        );
        $definition->addChannelDefinition($emailDef);
        $this->repository->save($definition);

        // Update with new channel
        $mobileDef = new MobileChannelDefinition(
            'mobile-template',
            ['type' => 'object', 'required' => ['message']],
            ['type' => 'object', 'required' => ['title']],
            1,
            false
        );
        $definition->addChannelDefinition($mobileDef);
        $this->repository->save($definition);

        // Verify update
        $retrieved = $this->repository->findByIdentifier('test.notification');
        $this->assertNotNull($retrieved);
        $this->assertCount(2, $retrieved->getChannelDefinitions());
        $this->assertNotNull($retrieved->getChannelDefinition('mobile'));
        $this->assertNotNull($retrieved->getChannelDefinition('email'));
    }
}
