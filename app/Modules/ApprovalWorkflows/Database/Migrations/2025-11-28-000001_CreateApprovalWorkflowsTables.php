<?php

namespace App\Modules\ApprovalWorkflows\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Approval Workflows (Maker-Checker) module tables.
 */
class CreateApprovalWorkflowsTables extends Migration
{
    public function up(): void
    {
        // approval_workflows - Workflow definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'description' => ['type' => 'TEXT', 'null' => true],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'trigger_conditions' => ['type' => 'JSON', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'code'], 'uk_school_code');
        $this->forge->addKey('entity_type', false, false, 'idx_entity_type');
        $this->forge->createTable('approval_workflows', true);

        // approval_stages - Workflow stages
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'workflow_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'sequence' => ['type' => 'INT', 'constraint' => 11],
            'approver_type' => ['type' => 'ENUM', 'constraint' => ['user', 'role', 'department_head', 'custom']],
            'approver_ids' => ['type' => 'JSON', 'null' => true],
            'approval_type' => ['type' => 'ENUM', 'constraint' => ['any', 'all', 'majority'], 'default' => 'any'],
            'min_approvers' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'timeout_hours' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'escalation_to' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['workflow_id', 'sequence'], false, false, 'idx_workflow_sequence');
        $this->forge->createTable('approval_stages', true);

        // approval_requests - Pending approval requests
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'workflow_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'current_stage_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'entity_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'request_data' => ['type' => 'JSON'],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'in_progress', 'approved', 'rejected', 'cancelled', 'expired'], 'default' => 'pending'],
            'priority' => ['type' => 'ENUM', 'constraint' => ['low', 'normal', 'high', 'urgent'], 'default' => 'normal'],
            'requested_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'requested_at' => ['type' => 'DATETIME'],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'status'], false, false, 'idx_school_status');
        $this->forge->addKey(['entity_type', 'entity_id'], false, false, 'idx_entity');
        $this->forge->addKey(['requested_by', 'status'], false, false, 'idx_requester_status');
        $this->forge->createTable('approval_requests', true);

        // approval_actions - Actions taken on requests
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'request_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'stage_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'action' => ['type' => 'ENUM', 'constraint' => ['approve', 'reject', 'delegate', 'request_info', 'comment']],
            'comments' => ['type' => 'TEXT', 'null' => true],
            'delegated_to' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'action_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'action_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['request_id', 'stage_id'], false, false, 'idx_request_stage');
        $this->forge->addKey('action_by', false, false, 'idx_action_by');
        $this->forge->createTable('approval_actions', true);

        // approval_delegations - Delegation of approval authority
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'from_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'to_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'workflow_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'start_date' => ['type' => 'DATE'],
            'end_date' => ['type' => 'DATE'],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['from_user_id', 'is_active'], false, false, 'idx_from_user_active');
        $this->forge->createTable('approval_delegations', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('approval_delegations', true);
        $this->forge->dropTable('approval_actions', true);
        $this->forge->dropTable('approval_requests', true);
        $this->forge->dropTable('approval_stages', true);
        $this->forge->dropTable('approval_workflows', true);
    }
}
