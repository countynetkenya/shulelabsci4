<?php

namespace App\Modules\Mobile\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Mobile module tables for mobile app support.
 */
class CreateMobileTables extends Migration
{
    public function up(): void
    {
        // mobile_devices - Registered mobile devices
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'device_id' => ['type' => 'VARCHAR', 'constraint' => 255],
            'device_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'device_type' => ['type' => 'ENUM', 'constraint' => ['ios', 'android', 'web']],
            'os_version' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'app_version' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_active_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'device_id'], 'uk_user_device');
        $this->forge->createTable('mobile_devices', true);

        // push_tokens - FCM/APNs tokens for push notifications
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'device_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'token' => ['type' => 'VARCHAR', 'constraint' => 500],
            'platform' => ['type' => 'ENUM', 'constraint' => ['fcm', 'apns', 'web']],
            'is_valid' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'failed_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'last_used_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token', 'uk_token');
        $this->forge->addKey('device_id', false, false, 'idx_device');
        $this->forge->createTable('push_tokens', true);

        // sync_snapshots - Offline sync data snapshots
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'device_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'snapshot_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'data_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
            'data' => ['type' => 'LONGTEXT'],
            'version' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'snapshot_type'], false, false, 'idx_user_type');
        $this->forge->addKey(['device_id', 'snapshot_type'], false, false, 'idx_device_type');
        $this->forge->createTable('sync_snapshots', true);

        // offline_queue - Pending offline operations
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'device_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'operation' => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'payload' => ['type' => 'JSON'],
            'client_timestamp' => ['type' => 'DATETIME'],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'processing', 'completed', 'failed', 'conflict'], 'default' => 'pending'],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['device_id', 'status'], false, false, 'idx_device_status');
        $this->forge->createTable('offline_queue', true);

        // api_tokens - JWT refresh tokens
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'device_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'token_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
            'expires_at' => ['type' => 'DATETIME'],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_hash', 'uk_token_hash');
        $this->forge->addKey(['user_id', 'expires_at'], false, false, 'idx_user_expires');
        $this->forge->createTable('api_tokens', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('api_tokens', true);
        $this->forge->dropTable('offline_queue', true);
        $this->forge->dropTable('sync_snapshots', true);
        $this->forge->dropTable('push_tokens', true);
        $this->forge->dropTable('mobile_devices', true);
    }
}
