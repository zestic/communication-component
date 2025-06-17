<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCommunicationTemplates extends AbstractMigration
{
    public function up(): void
    {
        // Create communication_templates table
        $communicationTemplates = $this->table('communication_templates', ['id' => false, 'primary_key' => 'id']);
        $communicationTemplates
            ->addColumn('id', 'string', ['limit' => 26])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('channel', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('subject', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('content', 'text', ['null' => false])
            ->addColumn('content_type', 'string', ['limit' => 50, 'null' => false, 'default' => 'text/html'])
            ->addColumn('metadata', 'jsonb', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at', 'timestamp', ['timezone' => true, 'default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['name', 'channel'], ['unique' => true])
            ->create();

        // Create trigger for updating updated_at column
        $this->execute("
            CREATE TRIGGER update_communication_templates_updated_at
                BEFORE UPDATE ON communication_templates
                FOR EACH ROW
                EXECUTE FUNCTION update_updated_at_column();
        ");
    }

    public function down(): void
    {
        $this->table('communication_templates')->drop()->save();
    }
}
