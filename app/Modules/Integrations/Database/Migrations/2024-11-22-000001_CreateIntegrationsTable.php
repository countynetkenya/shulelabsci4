<?php

namespace Modules\Integrations\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the integrations table to store integration configurations.
 */
class CreateIntegrationsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'payment, communication, storage, lms, etc.',
            ],
            'adapter_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'config_json' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Encrypted configuration JSON',
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'tenant_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('type');
        $this->forge->addKey('is_active');
        $this->forge->addKey('tenant_id');
        $this->forge->createTable('integration_integrations', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('integration_integrations', true);
    }
}
