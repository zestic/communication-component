<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCommunicationDefinitions extends AbstractMigration
{
    public function up(): void
    {
        // Create communication_definitions table
        $communicationDefinitions = $this->table('communication_definitions', ['id' => false, 'primary_key' => 'identifier']);
        $communicationDefinitions
            ->addColumn('identifier', 'string', ['limit' => 255])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->create();

        // Create channel_definitions table
        $channelDefinitions = $this->table('channel_definitions');
        $channelDefinitions
            ->addColumn('communication_identifier', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('channel', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('template', 'text', ['null' => false])
            ->addColumn('context_schema', 'jsonb', ['null' => false])
            ->addColumn('subject_schema', 'jsonb', ['null' => false])
            ->addColumn('channel_config', 'jsonb', ['null' => false, 'default' => '{}'])
            ->addColumn('created_at', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addForeignKey('communication_identifier', 'communication_definitions', 'identifier', ['delete' => 'CASCADE'])
            ->addIndex(['communication_identifier'], ['name' => 'idx_channel_definitions_communication_identifier'])
            ->addIndex(['channel'], ['name' => 'idx_channel_definitions_channel'])
            ->addIndex(['communication_identifier', 'channel'], ['unique' => true])
            ->create();

        // Create triggers for updating updated_at column (function should already exist)
        $this->execute("
            CREATE TRIGGER update_communication_definitions_updated_at
                BEFORE UPDATE ON communication_definitions
                FOR EACH ROW
                EXECUTE FUNCTION update_updated_at_column();

            CREATE TRIGGER update_channel_definitions_updated_at
                BEFORE UPDATE ON channel_definitions
                FOR EACH ROW
                EXECUTE FUNCTION update_updated_at_column();
        ");
    }

    public function down(): void
    {
        $this->table('channel_definitions')->drop()->save();
        $this->table('communication_definitions')->drop()->save();
    }
}
