<?php

namespace Modules\Foundation\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIntegrationRegistry extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'channel'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'tenant_id'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'idempotency_key' => ['type' => 'VARCHAR', 'constraint' => 191],
            'payload_json'    => ['type' => 'LONGTEXT', 'null' => true],
            'response_json'   => ['type' => 'LONGTEXT', 'null' => true],
            'status'          => ['type' => 'VARCHAR', 'constraint' => 32],
            'error_message'   => ['type' => 'TEXT', 'null' => true],
            'retry_after'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'queued_at'       => ['type' => 'DATETIME'],
            'completed_at'    => ['type' => 'DATETIME', 'null' => true],
            'failed_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['channel', 'idempotency_key'], false, true);
        $this->forge->addKey('status');
        $this->forge->createTable('integration_dispatches', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('integration_dispatches', true);
    }
}
