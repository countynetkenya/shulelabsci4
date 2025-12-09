<?php

namespace App\Modules\ApprovalWorkflows\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ApprovalSeeder extends Seeder
{
    public function run()
    {
        $schoolId = 1; // Default test school ID

        // 1. Create Approval Workflows
        $workflows = [
            [
                'school_id'          => $schoolId,
                'name'               => 'Purchase Request Approval',
                'code'               => 'PURCHASE_REQUEST',
                'description'        => 'Multi-level approval for purchase requests above KES 10,000',
                'entity_type'        => 'purchase_request',
                'trigger_conditions' => json_encode(['amount_threshold' => 10000]),
                'is_active'          => 1,
                'created_by'         => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'          => $schoolId,
                'name'               => 'Leave Request Approval',
                'code'               => 'LEAVE_REQUEST',
                'description'        => 'Staff leave request approval workflow',
                'entity_type'        => 'leave_request',
                'trigger_conditions' => null,
                'is_active'          => 1,
                'created_by'         => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('approval_workflows')->insertBatch($workflows);

        // 2. Create Approval Requests
        $requests = [
            [
                'school_id'        => $schoolId,
                'workflow_id'      => 1,
                'current_stage_id' => null,
                'entity_type'      => 'purchase_request',
                'entity_id'        => 101,
                'request_data'     => json_encode([
                    'item'     => 'Office Supplies',
                    'amount'   => 15000,
                    'vendor'   => 'ABC Stationers',
                    'delivery' => '2025-02-15',
                ]),
                'status'           => 'pending',
                'priority'         => 'normal',
                'requested_by'     => 1,
                'requested_at'     => '2025-01-15 09:00:00',
                'completed_at'     => null,
                'expires_at'       => '2025-02-15 23:59:59',
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $schoolId,
                'workflow_id'      => 1,
                'current_stage_id' => null,
                'entity_type'      => 'purchase_request',
                'entity_id'        => 102,
                'request_data'     => json_encode([
                    'item'     => 'Computer Equipment',
                    'amount'   => 50000,
                    'vendor'   => 'Tech Solutions Ltd',
                    'delivery' => '2025-03-01',
                ]),
                'status'           => 'in_progress',
                'priority'         => 'high',
                'requested_by'     => 1,
                'requested_at'     => '2025-01-20 10:30:00',
                'completed_at'     => null,
                'expires_at'       => '2025-03-01 23:59:59',
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $schoolId,
                'workflow_id'      => 2,
                'current_stage_id' => null,
                'entity_type'      => 'leave_request',
                'entity_id'        => 201,
                'request_data'     => json_encode([
                    'employee'    => 'John Doe',
                    'leave_type'  => 'Annual Leave',
                    'start_date'  => '2025-03-10',
                    'end_date'    => '2025-03-15',
                    'days_count'  => 5,
                ]),
                'status'           => 'approved',
                'priority'         => 'normal',
                'requested_by'     => 2,
                'requested_at'     => '2025-01-10 14:00:00',
                'completed_at'     => '2025-01-12 09:30:00',
                'expires_at'       => null,
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $schoolId,
                'workflow_id'      => 2,
                'current_stage_id' => null,
                'entity_type'      => 'leave_request',
                'entity_id'        => 202,
                'request_data'     => json_encode([
                    'employee'    => 'Jane Smith',
                    'leave_type'  => 'Sick Leave',
                    'start_date'  => '2025-02-05',
                    'end_date'    => '2025-02-07',
                    'days_count'  => 3,
                ]),
                'status'           => 'rejected',
                'priority'         => 'urgent',
                'requested_by'     => 3,
                'requested_at'     => '2025-02-04 16:45:00',
                'completed_at'     => '2025-02-05 08:00:00',
                'expires_at'       => null,
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'        => $schoolId,
                'workflow_id'      => 1,
                'current_stage_id' => null,
                'entity_type'      => 'purchase_request',
                'entity_id'        => 103,
                'request_data'     => json_encode([
                    'item'     => 'Sports Equipment',
                    'amount'   => 25000,
                    'vendor'   => 'Sports World',
                    'delivery' => '2025-02-20',
                ]),
                'status'           => 'pending',
                'priority'         => 'low',
                'requested_by'     => 1,
                'requested_at'     => '2025-01-25 11:15:00',
                'completed_at'     => null,
                'expires_at'       => '2025-02-20 23:59:59',
                'created_at'       => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('approval_requests')->insertBatch($requests);
    }
}
