<?php

namespace Modules\Integrations\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the integration_auth_tokens table for OAuth and API token storage.
 */
class CreateIntegrationAuthTokensTable extends Migration
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
            'token_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'bearer, oauth2, api_key, etc.',
            ],
            'access_token' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'refresh_token' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'scope' => [
                'type' => 'TEXT',
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
        $this->forge->addKey(['integration_id']);
        $this->forge->addKey(['expires_at']);
        $this->forge->createTable('ci4_integration_auth_tokens', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ci4_integration_auth_tokens', true);
    }
}
