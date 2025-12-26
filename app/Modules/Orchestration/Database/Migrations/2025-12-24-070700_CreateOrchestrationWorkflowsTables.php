<?php

namespace Modules\Orchestration\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Orchestration workflow tables.
 */
class CreateOrchestrationWorkflowsTables extends Migration
{
    public function up(): void
    {
        // workflows - Workflow definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'workflow_id' => ['type' => 'VARCHAR', 'constraint' => 100],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'description' => ['type' => 'TEXT', 'null' => true],
            'steps' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'running', 'completed', 'failed', 'paused'], 'default' => 'pending'],
            'current_step' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'total_steps' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'started_at' => ['type' => 'DATETIME', 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'workflow_id'], 'uk_school_workflow');
        $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
        $this->forge->createTable('workflows', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('workflows', true);
    }
}
