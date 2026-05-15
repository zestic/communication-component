<?php

declare(strict_types=1);

namespace Tests\Integration\Communication\Definition\Repository;

use Communication\Definition\CommunicationDefinition;
use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\MobileChannelDefinition;
use Communication\Definition\Repository\MySqlCommunicationDefinitionRepository;
use PDO;
use Tests\Integration\IntegrationTestCase;

class MySqlCommunicationDefinitionRepositoryTest extends IntegrationTestCase
{
    private PDO $pdo;

    private MySqlCommunicationDefinitionRepository $repository;

    protected function setUp(): void
    {
        // Get database connection parameters from environment variables
        $host = getenv('MYSQL_TEST_HOST') ?: 'localhost';
        $port = getenv('MYSQL_TEST_PORT') ?: '3306';
        $dbname = getenv('MYSQL_TEST_DB') ?: 'communication_component_test';
        $user = getenv('MYSQL_TEST_USER') ?: 'root';
        $password = getenv('MYSQL_TEST_PASSWORD') ?: '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $this->pdo = new PDO(
            $dsn,
            $user ?: null,
            $password ?: null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        // Drop tables if they exist to ensure a clean state
        $this->pdo->exec("DROP TABLE IF EXISTS channel_definitions");
        $this->pdo->exec("DROP TABLE IF EXISTS communication_definitions");

        // Create tables
        $this->createTables();

        $this->repository = new MySqlCommunicationDefinitionRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS channel_definitions");
        $this->pdo->exec("DROP TABLE IF EXISTS communication_definitions");
    }

    private function createTables(): void
    {
        // Create communication_definitions table
        $this->pdo->exec("
            CREATE TABLE communication_definitions (
                identifier VARCHAR(255) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create channel_definitions table
        $this->pdo->exec("
            CREATE TABLE channel_definitions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                communication_identifier VARCHAR(255) NOT NULL,
                channel VARCHAR(50) NOT NULL,
                template TEXT NOT NULL,
                context_schema JSON NOT NULL,
                subject_schema JSON NOT NULL,
                channel_config JSON NOT NULL DEFAULT '{}',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
                FOREIGN KEY (communication_identifier) REFERENCES communication_definitions(identifier) ON DELETE CASCADE,
                UNIQUE KEY unique_communication_channel (communication_identifier, channel),
                INDEX idx_communication_identifier (communication_identifier),
                INDEX idx_channel (channel)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
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
    }
}
