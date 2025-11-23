<?php

namespace Modules\Integrations\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the integration_webhooks table for webhook configuration.
 */
class CreateIntegrationWebhooksTable extends Migration
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
            ],
            'webhook_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'payment.success, sms.delivered, etc.',
            ],
            'secret' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'default' => 1,
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
        $this->forge->addKey(['integration_id']);
        $this->forge->addKey(['event_type']);
        $this->forge->addKey(['is_active']);
        $this->forge->createTable('integration_webhooks', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('integration_webhooks', true);
    }
}
