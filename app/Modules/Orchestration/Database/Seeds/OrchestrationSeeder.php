<?php

namespace Modules\Orchestration\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * OrchestrationSeeder - Seed sample workflow records.
 */
class OrchestrationSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'school_id' => 1,
                'workflow_id' => 'wf_daily_backup_001',
                'name' => 'Daily Backup Workflow',
                'description' => 'Automated daily backup of school database',
                'steps' => "Check system health\nInitiate backup\nVerify backup integrity\nClean old backups\nSend completion notification",
                'status' => 'completed',
                'current_step' => 5,
                'total_steps' => 5,
                'started_at' => date('Y-m-d 02:00:00'),
                'completed_at' => date('Y-m-d 02:15:00'),
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
                'updated_at' => date('Y-m-d 02:15:00'),
            ],
            [
                'school_id' => 1,
                'workflow_id' => 'wf_student_sync_002',
                'name' => 'Student Data Sync',
                'description' => 'Synchronize student data across modules',
                'steps' => "Connect to LMS\nFetch student updates\nValidate data\nUpdate local database\nTrigger notifications",
                'status' => 'running',
                'current_step' => 3,
                'total_steps' => 5,
                'started_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'completed_at' => null,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            ],
            [
                'school_id' => 1,
                'workflow_id' => 'wf_report_gen_003',
                'name' => 'Monthly Report Generation',
                'description' => 'Generate and distribute monthly performance reports',
                'steps' => "Collect data\nGenerate reports\nQuality check\nSend to stakeholders",
                'status' => 'pending',
                'current_step' => 0,
                'total_steps' => 4,
                'started_at' => null,
                'completed_at' => null,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'school_id' => 1,
                'workflow_id' => 'wf_mobile_push_004',
                'name' => 'Mobile Notifications Workflow',
                'description' => 'Send push notifications to mobile app users',
                'steps' => "Prepare message\nTarget user selection\nSend notifications\nTrack delivery",
                'status' => 'completed',
                'current_step' => 4,
                'total_steps' => 4,
                'started_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                'completed_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
            [
                'school_id' => 1,
                'workflow_id' => 'wf_exam_process_005',
                'name' => 'Exam Processing Workflow',
                'description' => 'Process and grade exam submissions',
                'steps' => "Collect submissions\nValidate format\nAuto-grade MCQs\nManual review\nPublish results",
                'status' => 'failed',
                'current_step' => 2,
                'total_steps' => 5,
                'started_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'completed_at' => null,
                'error_message' => 'Failed to connect to grading service',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
        ];

        $this->db->table('workflows')->insertBatch($data);
    }
}
