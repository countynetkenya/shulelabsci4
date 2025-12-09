<?php

namespace Modules\Scheduler\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * SchedulerSeeder - Seeds sample scheduled jobs for testing
 */
class SchedulerSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        $this->db->table('scheduled_jobs')->truncate();

        $data = [
            [
                'school_id' => 1,
                'name' => 'Daily Attendance Report',
                'description' => 'Generates daily attendance summary and sends to administrators',
                'job_class' => 'App\Jobs\Reports\AttendanceReportJob',
                'job_method' => 'handle',
                'parameters' => json_encode(['report_type' => 'daily']),
                'cron_expression' => '0 8 * * *', // Every day at 8 AM
                'timezone' => 'Africa/Nairobi',
                'is_active' => 1,
                'max_retries' => 3,
                'retry_delay_seconds' => 300,
                'timeout_seconds' => 1800,
                'overlap_prevention' => 1,
                'next_run_at' => date('Y-m-d 08:00:00', strtotime('+1 day')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Cleanup Old Sessions',
                'description' => 'Removes expired user sessions from the database',
                'job_class' => 'App\Jobs\Maintenance\CleanupSessionsJob',
                'job_method' => 'handle',
                'parameters' => json_encode(['days_old' => 7]),
                'cron_expression' => '0 2 * * 0', // Every Sunday at 2 AM
                'timezone' => 'Africa/Nairobi',
                'is_active' => 1,
                'max_retries' => 2,
                'retry_delay_seconds' => 600,
                'timeout_seconds' => 3600,
                'overlap_prevention' => 1,
                'next_run_at' => date('Y-m-d 02:00:00', strtotime('next Sunday')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Weekly Fee Reminders',
                'description' => 'Sends fee payment reminders to parents via SMS',
                'job_class' => 'App\Jobs\Finance\FeeReminderJob',
                'job_method' => 'handle',
                'parameters' => json_encode(['reminder_type' => 'weekly']),
                'cron_expression' => '0 9 * * 1', // Every Monday at 9 AM
                'timezone' => 'Africa/Nairobi',
                'is_active' => 1,
                'max_retries' => 3,
                'retry_delay_seconds' => 300,
                'timeout_seconds' => 2400,
                'overlap_prevention' => 1,
                'next_run_at' => date('Y-m-d 09:00:00', strtotime('next Monday')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Database Backup',
                'description' => 'Creates automated database backup',
                'job_class' => 'App\Jobs\Backup\DatabaseBackupJob',
                'job_method' => 'handle',
                'parameters' => json_encode(['compression' => 'gzip']),
                'cron_expression' => '0 1 * * *', // Every day at 1 AM
                'timezone' => 'Africa/Nairobi',
                'is_active' => 1,
                'max_retries' => 2,
                'retry_delay_seconds' => 900,
                'timeout_seconds' => 7200,
                'overlap_prevention' => 1,
                'next_run_at' => date('Y-m-d 01:00:00', strtotime('+1 day')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Library Overdue Notices',
                'description' => 'Sends overdue book return notices to students',
                'job_class' => 'App\Jobs\Library\OverdueNoticesJob',
                'job_method' => 'handle',
                'parameters' => json_encode(['grace_days' => 3]),
                'cron_expression' => '0 10 * * 2,4', // Every Tuesday and Thursday at 10 AM
                'timezone' => 'Africa/Nairobi',
                'is_active' => 1,
                'max_retries' => 3,
                'retry_delay_seconds' => 300,
                'timeout_seconds' => 1800,
                'overlap_prevention' => 1,
                'next_run_at' => date('Y-m-d 10:00:00', strtotime('next Tuesday')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('scheduled_jobs')->insertBatch($data);

        echo "Inserted " . count($data) . " scheduled jobs.\n";
    }
}
