<?php

namespace Modules\Reports\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ReportsSeeder - Populates sample reports data.
 */
class ReportsSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'school_id'          => 1,
                'name'               => 'Monthly Student Performance Report',
                'description'        => 'Comprehensive report showing student academic performance trends',
                'template'           => 'student_performance',
                'parameters'         => json_encode(['period' => 'monthly', 'include_charts' => true]),
                'format'             => 'pdf',
                'schedule'           => '0 8 1 * *',
                'is_scheduled'       => 1,
                'status'             => 'active',
                'created_by'         => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'          => 1,
                'name'               => 'Weekly Attendance Summary',
                'description'        => 'Weekly attendance statistics for all classes',
                'template'           => 'attendance_summary',
                'parameters'         => json_encode(['groupBy' => 'class', 'include_absences' => true]),
                'format'             => 'excel',
                'schedule'           => '0 9 * * 1',
                'is_scheduled'       => 1,
                'status'             => 'active',
                'created_by'         => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'          => 1,
                'name'               => 'Financial Overview',
                'description'        => 'Monthly financial report with income and expenses',
                'template'           => 'financial_overview',
                'parameters'         => json_encode(['include_projections' => true]),
                'format'             => 'pdf',
                'schedule'           => null,
                'is_scheduled'       => 0,
                'status'             => 'draft',
                'created_by'         => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'          => 1,
                'name'               => 'Library Circulation Report',
                'description'        => 'Books borrowed, returned, and overdue items',
                'template'           => 'library_circulation',
                'parameters'         => json_encode(['period' => 'weekly']),
                'format'             => 'csv',
                'schedule'           => '0 10 * * 5',
                'is_scheduled'       => 1,
                'status'             => 'active',
                'created_by'         => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'          => 1,
                'name'               => 'Inventory Status Report',
                'description'        => 'Current stock levels and reorder notifications',
                'template'           => 'inventory_status',
                'parameters'         => json_encode(['include_low_stock' => true]),
                'format'             => 'pdf',
                'schedule'           => null,
                'is_scheduled'       => 0,
                'status'             => 'active',
                'created_by'         => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('reports')->insertBatch($data);

        echo 'Inserted ' . count($data) . " sample reports.\n";
    }
}
