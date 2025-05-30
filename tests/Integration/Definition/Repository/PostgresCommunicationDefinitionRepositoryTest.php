<?php

declare(strict_types=1);

namespace Tests\Integration\Communication\Definition\Repository;

use Communication\Definition\CommunicationDefinition;
use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\MobileChannelDefinition;
use Communication\Definition\Repository\PostgresCommunicationDefinitionRepository;
use PDO;
use Tests\Integration\IntegrationTestCase;

class PostgresCommunicationDefinitionRepositoryTest extends IntegrationTestCase
{
    private PDO $pdo;

    private PostgresCommunicationDefinitionRepository $repository;

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

        // Drop tables if they exist to ensure a clean state
        $this->pdo->exec("DROP TABLE IF EXISTS {$schema}.channel_definitions");
        $this->pdo->exec("DROP TABLE IF EXISTS {$schema}.communication_definitions");

        // Create tables manually since Phinx migrations are not working properly
        $this->createTables($schema ?: 'communication_component');

        $this->repository = new PostgresCommunicationDefinitionRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        $schema = getenv('POSTGRES_SCHEMA') ?: 'communication_component';
        $this->pdo->exec("DROP TABLE IF EXISTS {$schema}.channel_definitions");
        $this->pdo->exec("DROP TABLE IF EXISTS {$schema}.communication_definitions");
    }

    private function createTables(string $schema): void
    {
        // Create communication_definitions table
        $this->pdo->exec("
            CREATE TABLE {$schema}.communication_definitions (
                identifier VARCHAR(255) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
            )
        ");

        // Create channel_definitions table
        $this->pdo->exec("
            CREATE TABLE {$schema}.channel_definitions (
                id SERIAL PRIMARY KEY,
                communication_identifier VARCHAR(255) NOT NULL,
                channel VARCHAR(50) NOT NULL,
                template TEXT NOT NULL,
                context_schema JSONB NOT NULL,
                subject_schema JSONB NOT NULL,
                channel_config JSONB NOT NULL DEFAULT '{}',
                created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
                FOREIGN KEY (communication_identifier) REFERENCES {$schema}.communication_definitions(identifier) ON DELETE CASCADE,
                UNIQUE (communication_identifier, channel)
            )
        ");

        // Create indexes
        $this->pdo->exec("CREATE INDEX idx_channel_definitions_communication_identifier ON {$schema}.channel_definitions(communication_identifier)");
        $this->pdo->exec("CREATE INDEX idx_channel_definitions_channel ON {$schema}.channel_definitions(channel)");
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
