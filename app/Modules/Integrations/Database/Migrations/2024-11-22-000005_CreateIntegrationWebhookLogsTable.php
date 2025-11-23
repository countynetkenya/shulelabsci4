<?php

namespace Modules\Integrations\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the integration_webhook_logs table for webhook event tracking.
 */
class CreateIntegrationWebhookLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'webhook_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'adapter_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'payload' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'received, processing, completed, failed',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'signature_valid' => [
                'type'    => 'TINYINT',
                'null'    => true,
                'comment' => 'null if not verified, 1 if valid, 0 if invalid',
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['webhook_id']);
        $this->forge->addKey(['adapter_name', 'event_type']);
        $this->forge->addKey(['status']);
        $this->forge->addKey(['created_at']);
        $this->forge->createTable('integration_webhook_logs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('integration_webhook_logs', true);
    }
}
