<?php

namespace App\Modules\Scheduler\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the scheduler module tables: scheduled_jobs, job_runs, job_logs, job_queue, job_failed.
 */
class CreateSchedulerTables extends Migration
{
    public function up(): void
    {
        // scheduled_jobs - Defines recurring scheduled tasks
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'job_class' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'job_method' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'handle',
            ],
            'parameters' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'cron_expression' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'timezone' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Africa/Nairobi',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'run_as_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'max_retries' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 3,
            ],
            'retry_delay_seconds' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 60,
            ],
            'timeout_seconds' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 3600,
            ],
            'overlap_prevention' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'last_run_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_run_status' => [
                'type' => 'ENUM',
                'constraint' => ['success', 'failed', 'running', 'timeout'],
                'null' => true,
            ],
            'next_run_at' => [
                'type' => 'DATETIME',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['next_run_at', 'is_active'], false, false, 'idx_next_run');
        $this->forge->addKey('school_id', false, false, 'idx_school');
        $this->forge->createTable('scheduled_jobs', true);

        // job_runs - Execution history for scheduled jobs
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'scheduled_job_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'job_class' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'job_method' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'parameters' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'run_type' => [
                'type' => 'ENUM',
                'constraint' => ['scheduled', 'manual', 'queued', 'retry'],
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'running', 'success', 'failed', 'cancelled', 'timeout'],
                'default' => 'pending',
            ],
            'attempt_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'duration_ms' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'result_summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'error_trace' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'output_log' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'triggered_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'worker_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['scheduled_job_id', 'status'], false, false, 'idx_job_status');
        $this->forge->addKey(['school_id', 'created_at'], false, false, 'idx_school_date');
        $this->forge->addKey(['status', 'created_at'], false, false, 'idx_status_created');
        $this->forge->createTable('job_runs', true);

        // job_logs - Detailed execution logs for debugging
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'job_run_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'level' => [
                'type' => 'ENUM',
                'constraint' => ['debug', 'info', 'warning', 'error'],
                'default' => 'info',
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'context' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'logged_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['job_run_id', 'level'], false, false, 'idx_run_level');
        $this->forge->createTable('job_logs', true);

        // job_queue - Queue for background jobs
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'queue_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'default',
            ],
            'job_class' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'job_method' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'handle',
            ],
            'payload' => [
                'type' => 'JSON',
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'priority' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'max_attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 3,
            ],
            'available_at' => [
                'type' => 'DATETIME',
            ],
            'reserved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'reserved_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['queue_name', 'available_at', 'reserved_at'], false, false, 'idx_queue_available');
        $this->forge->addKey(['priority', 'available_at'], false, false, 'idx_priority');
        $this->forge->createTable('job_queue', true);

        // job_failed - Failed jobs for inspection and retry
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'queue_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'job_class' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'payload' => [
                'type' => 'JSON',
            ],
            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'exception' => [
                'type' => 'TEXT',
            ],
            'failed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'failed_at'], false, false, 'idx_school_failed');
        $this->forge->createTable('job_failed', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('job_failed', true);
        $this->forge->dropTable('job_queue', true);
        $this->forge->dropTable('job_logs', true);
        $this->forge->dropTable('job_runs', true);
        $this->forge->dropTable('scheduled_jobs', true);
    }
}
