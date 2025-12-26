<?php

namespace App\Modules\Analytics\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * AnalyticsSeeder - Seed sample analytics dashboards.
 */
class AnalyticsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Sample dashboards for school_id = 1
        $dashboards = [
            [
                'school_id' => 1,
                'name' => 'School Performance Overview',
                'description' => 'Main dashboard showing key performance indicators for the entire school',
                'layout' => json_encode([
                    'widgets' => [
                        ['type' => 'metric', 'title' => 'Total Students', 'position' => ['row' => 1, 'col' => 1]],
                        ['type' => 'chart', 'title' => 'Attendance Trends', 'position' => ['row' => 1, 'col' => 2]],
                        ['type' => 'chart', 'title' => 'Academic Performance', 'position' => ['row' => 2, 'col' => 1]],
                    ],
                ]),
                'is_default' => 1,
                'is_shared' => 1,
                'shared_with_roles' => json_encode(['admin', 'principal']),
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            ],
            [
                'school_id' => 1,
                'name' => 'Financial Summary',
                'description' => 'Dashboard tracking fee collection, expenses, and financial health',
                'layout' => json_encode([
                    'widgets' => [
                        ['type' => 'metric', 'title' => 'Total Revenue', 'position' => ['row' => 1, 'col' => 1]],
                        ['type' => 'metric', 'title' => 'Outstanding Fees', 'position' => ['row' => 1, 'col' => 2]],
                        ['type' => 'chart', 'title' => 'Monthly Revenue', 'position' => ['row' => 2, 'col' => 1]],
                    ],
                ]),
                'is_default' => 0,
                'is_shared' => 1,
                'shared_with_roles' => json_encode(['admin', 'bursar']),
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
            ],
            [
                'school_id' => 1,
                'name' => 'Academic Progress Tracker',
                'description' => 'Monitor student academic progress across all classes and subjects',
                'layout' => json_encode([
                    'widgets' => [
                        ['type' => 'table', 'title' => 'Top Performers', 'position' => ['row' => 1, 'col' => 1]],
                        ['type' => 'chart', 'title' => 'Subject Performance', 'position' => ['row' => 1, 'col' => 2]],
                        ['type' => 'list', 'title' => 'At-Risk Students', 'position' => ['row' => 2, 'col' => 1]],
                    ],
                ]),
                'is_default' => 0,
                'is_shared' => 1,
                'shared_with_roles' => json_encode(['admin', 'teacher', 'hod']),
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
            ],
            [
                'school_id' => 1,
                'name' => 'Attendance & Discipline',
                'description' => 'Track student attendance patterns and disciplinary issues',
                'layout' => json_encode([
                    'widgets' => [
                        ['type' => 'metric', 'title' => 'Attendance Rate', 'position' => ['row' => 1, 'col' => 1]],
                        ['type' => 'chart', 'title' => 'Daily Attendance', 'position' => ['row' => 1, 'col' => 2]],
                        ['type' => 'list', 'title' => 'Recent Incidents', 'position' => ['row' => 2, 'col' => 1]],
                    ],
                ]),
                'is_default' => 0,
                'is_shared' => 1,
                'shared_with_roles' => json_encode(['admin', 'dean']),
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            ],
            [
                'school_id' => 1,
                'name' => 'Enrollment Trends',
                'description' => 'Analyze enrollment patterns and projections',
                'layout' => json_encode([
                    'widgets' => [
                        ['type' => 'chart', 'title' => 'Enrollment by Year', 'position' => ['row' => 1, 'col' => 1]],
                        ['type' => 'chart', 'title' => 'Class Size Distribution', 'position' => ['row' => 1, 'col' => 2]],
                        ['type' => 'metric', 'title' => 'Current Capacity', 'position' => ['row' => 2, 'col' => 1]],
                    ],
                ]),
                'is_default' => 0,
                'is_shared' => 0,
                'shared_with_roles' => null,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
        ];

        // Insert dashboards
        foreach ($dashboards as $dashboard) {
            $db->table('analytics_dashboards')->insert($dashboard);
        }

        echo 'Inserted ' . count($dashboards) . " sample analytics dashboards.\n";
    }
}
