<?php

declare(strict_types=1);

namespace Modules\Reports\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportsTables extends Migration
{
    public function up(): void
    {
        // Reports table
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'description'  => ['type' => 'TEXT', 'null' => true],
            'type'         => ['type' => 'VARCHAR', 'constraint' => 50],
            'config_json'  => ['type' => 'LONGTEXT'],
            'owner_id'     => ['type' => 'VARCHAR', 'constraint' => 64],
            'tenant_id'    => ['type' => 'VARCHAR', 'constraint' => 64],
            'template_ref' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'is_public'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_active'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME'],
            'updated_at'   => ['type' => 'DATETIME'],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('owner_id');
        $this->forge->addKey(['tenant_id', 'is_active']);
        $this->forge->createTable('ci4_reports_reports', true);

        // Report Templates table
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'category'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'module'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'config_json' => ['type' => 'LONGTEXT'],
            'is_system'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME'],
            'updated_at'  => ['type' => 'DATETIME'],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('module');
        $this->forge->addKey(['category', 'is_active']);
        $this->forge->createTable('ci4_reports_templates', true);

        // Report Filters table
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'report_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'filter_json' => ['type' => 'TEXT'],
            'is_default'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'  => ['type' => 'DATETIME'],
            'updated_at'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('report_id');
        $this->forge->addForeignKey('report_id', 'ci4_reports_reports', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ci4_reports_filters', true);

        // Report Results table (cached results)
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'report_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'filter_hash'  => ['type' => 'VARCHAR', 'constraint' => 64],
            'result_data'  => ['type' => 'LONGTEXT'],
            'row_count'    => ['type' => 'INT', 'unsigned' => true],
            'generated_at' => ['type' => 'DATETIME'],
            'expires_at'   => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('report_id');
        $this->forge->addKey(['report_id', 'filter_hash']);
        $this->forge->addKey('expires_at');
        $this->forge->addForeignKey('report_id', 'ci4_reports_reports', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ci4_reports_results', true);

        // Report Schedules table
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'report_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'frequency'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'format'        => ['type' => 'VARCHAR', 'constraint' => 20],
            'recipients'    => ['type' => 'TEXT'],
            'schedule_data' => ['type' => 'TEXT', 'null' => true],
            'last_run_at'   => ['type' => 'DATETIME', 'null' => true],
            'next_run_at'   => ['type' => 'DATETIME', 'null' => true],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME'],
            'updated_at'    => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('report_id');
        $this->forge->addKey(['is_active', 'next_run_at']);
        $this->forge->addForeignKey('report_id', 'ci4_reports_reports', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ci4_reports_schedules', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ci4_reports_schedules', true);
        $this->forge->dropTable('ci4_reports_results', true);
        $this->forge->dropTable('ci4_reports_filters', true);
        $this->forge->dropTable('ci4_reports_templates', true);
        $this->forge->dropTable('ci4_reports_reports', true);
    }
}
