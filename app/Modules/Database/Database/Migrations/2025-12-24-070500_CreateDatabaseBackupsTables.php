<?php

namespace Modules\Database\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Database module tables for backup management.
 */
class CreateDatabaseBackupsTables extends Migration
{
    public function up(): void
    {
        // db_backups - Database backup records
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'backup_id' => ['type' => 'VARCHAR', 'constraint' => 100],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'size' => ['type' => 'BIGINT', 'constraint' => 20, 'default' => 0],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'in_progress', 'completed', 'failed'], 'default' => 'pending'],
            'type' => ['type' => 'ENUM', 'constraint' => ['full', 'incremental', 'differential'], 'default' => 'full'],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'started_at' => ['type' => 'DATETIME', 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'backup_id'], 'uk_school_backup');
        $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
        $this->forge->createTable('db_backups', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('db_backups', true);
    }
}
