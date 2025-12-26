<?php

namespace Modules\Database\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder - Seed sample backup records.
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'school_id' => 1,
                'backup_id' => 'backup_20251224_001',
                'name' => 'Daily Backup - Dec 24, 2025',
                'path' => '/backups/1/2025-12-24/daily_backup.sql',
                'size' => 15728640, // 15 MB
                'status' => 'completed',
                'type' => 'full',
                'started_at' => '2025-12-24 02:00:00',
                'completed_at' => '2025-12-24 02:15:00',
                'created_at' => '2025-12-24 02:00:00',
                'updated_at' => '2025-12-24 02:15:00',
            ],
            [
                'school_id' => 1,
                'backup_id' => 'backup_20251223_001',
                'name' => 'Weekly Backup - Week 51',
                'path' => '/backups/1/2025-12-23/weekly_backup.sql',
                'size' => 52428800, // 50 MB
                'status' => 'completed',
                'type' => 'full',
                'started_at' => '2025-12-23 02:00:00',
                'completed_at' => '2025-12-23 02:45:00',
                'created_at' => '2025-12-23 02:00:00',
                'updated_at' => '2025-12-23 02:45:00',
            ],
            [
                'school_id' => 1,
                'backup_id' => 'backup_20251222_001',
                'name' => 'Incremental Backup - Dec 22',
                'path' => '/backups/1/2025-12-22/incremental_backup.sql',
                'size' => 5242880, // 5 MB
                'status' => 'completed',
                'type' => 'incremental',
                'started_at' => '2025-12-22 02:00:00',
                'completed_at' => '2025-12-22 02:05:00',
                'created_at' => '2025-12-22 02:00:00',
                'updated_at' => '2025-12-22 02:05:00',
            ],
            [
                'school_id' => 1,
                'backup_id' => 'backup_20251221_001',
                'name' => 'Manual Backup - Before Update',
                'path' => '/backups/1/2025-12-21/manual_backup.sql',
                'size' => 45097156, // 43 MB
                'status' => 'completed',
                'type' => 'full',
                'started_at' => '2025-12-21 14:30:00',
                'completed_at' => '2025-12-21 15:00:00',
                'created_at' => '2025-12-21 14:30:00',
                'updated_at' => '2025-12-21 15:00:00',
            ],
            [
                'school_id' => 1,
                'backup_id' => 'backup_20251220_001',
                'name' => 'Test Backup - In Progress',
                'path' => '/backups/1/2025-12-20/test_backup.sql',
                'size' => 0,
                'status' => 'in_progress',
                'type' => 'differential',
                'started_at' => '2025-12-20 10:00:00',
                'completed_at' => null,
                'created_at' => '2025-12-20 10:00:00',
                'updated_at' => '2025-12-20 10:00:00',
            ],
        ];

        $this->db->table('db_backups')->insertBatch($data);
    }
}
