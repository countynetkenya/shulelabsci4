<?php

namespace Modules\Reports\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Reports module tables.
 */
class CreateReportsTables extends Migration
{
    public function up(): void
    {
        // reports - Report generation, templates, scheduling
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'template' => ['type' => 'VARCHAR', 'constraint' => 100],
            'parameters' => ['type' => 'JSON', 'null' => true],
            'format' => ['type' => 'ENUM', 'constraint' => ['pdf', 'excel', 'csv', 'html'], 'default' => 'pdf'],
            'schedule' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_scheduled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'last_generated_at' => ['type' => 'DATETIME', 'null' => true],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'active', 'archived'], 'default' => 'draft'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
        $this->forge->createTable('reports', true);

        // report_executions - Track report generation history
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'report_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'generated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'processing', 'completed', 'failed'], 'default' => 'pending'],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'file_size' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'started_at' => ['type' => 'DATETIME', 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('report_id', false, false, 'idx_report');
        $this->forge->createTable('report_executions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('report_executions', true);
        $this->forge->dropTable('reports', true);
    }
}
