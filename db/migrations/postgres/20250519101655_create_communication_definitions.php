<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCommunicationDefinitions extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
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

        // Create trigger for updating updated_at column
        $this->execute("
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$ language 'plpgsql';

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
}
