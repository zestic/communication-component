<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCommunicationTemplates extends AbstractMigration
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

        // Create trigger for updating updated_at column if it doesn't exist
        $this->execute("
            CREATE TRIGGER update_communication_templates_updated_at
                BEFORE UPDATE ON communication_templates
                FOR EACH ROW
                EXECUTE FUNCTION update_updated_at_column();
        ");
    }
}
