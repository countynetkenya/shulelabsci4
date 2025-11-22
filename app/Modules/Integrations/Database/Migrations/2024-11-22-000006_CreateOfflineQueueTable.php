<?php

namespace Modules\Integrations\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the offline_queue table for mobile/offline sync support.
 */
class CreateOfflineQueueTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'adapter_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'operation' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'payload' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'context' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'queued',
                'comment'    => 'queued, processing, completed, failed',
            ],
            'retry_count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'max_retries' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 3,
            ],
            'last_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tenant_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'priority' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 5,
                'comment'  => '1=highest, 10=lowest',
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'priority', 'scheduled_at']);
        $this->forge->addKey(['adapter_name']);
        $this->forge->addKey(['tenant_id', 'user_id']);
        $this->forge->addKey(['created_at']);
        $this->forge->createTable('ci4_integration_offline_queue', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ci4_integration_offline_queue', true);
    }
}
