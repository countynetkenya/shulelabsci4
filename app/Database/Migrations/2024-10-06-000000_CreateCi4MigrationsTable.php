<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Create CI4 Migrations History Table.
 *
 * This migration creates the migrations table that will store CI4's
 * migration history, separate from the legacy CI3 'migrations' table.
 *
 * CI3's migrations table uses a different structure (no 'id' column) that
 * is incompatible with CI4's MigrationRunner. By using a dedicated table,
 * both CI3 and CI4 can maintain their own migration histories independently.
 *
 * This migration is idempotent and safe to run multiple times.
 */
class CreateCi4MigrationsTable extends Migration
{
    /**
     * Create the migrations table with the standard CI4 schema.
     *
     * The table structure matches what CI4's MigrationRunner expects:
     * - id: Auto-increment primary key (required by CI4, missing in CI3)
     * - version: Migration timestamp (e.g., 2024-10-06-000001)
     * - class: Fully qualified class name of the migration
     * - group: Migration group identifier (default or custom)
     * - namespace: Namespace of the migration (App, Modules\Foundation, etc.)
     * - time: Unix timestamp when migration was applied
     * - batch: Batch number for rollback grouping
     */
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'class' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'group' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'namespace' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'time' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'batch' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);

        // Use ifNotExists to make this migration idempotent
        // Safe to run multiple times without errors
        $this->forge->createTable('migrations', true);
    }

    /**
     * Drop the migrations table.
     *
     * WARNING: This will delete all CI4 migration history.
     * Only use during development or when completely resetting CI4.
     */
    public function down()
    {
        $this->forge->dropTable('migrations', true);
    }
}
