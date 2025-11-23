<?php

namespace Modules\Integrations\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the integration_logs table for detailed operation logging.
 */
class CreateIntegrationLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'integration_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'adapter_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'operation' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'request_payload' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'response_payload' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'http_status' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'idempotency_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
                'null'       => true,
            ],
            'duration_ms' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
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
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['integration_id']);
        $this->forge->addKey(['adapter_name', 'operation']);
        $this->forge->addKey(['idempotency_key']);
        $this->forge->addKey(['tenant_id']);
        $this->forge->addKey(['created_at']);
        $this->forge->createTable('integration_logs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('integration_logs', true);
    }
}
