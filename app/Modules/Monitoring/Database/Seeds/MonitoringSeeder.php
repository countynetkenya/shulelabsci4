<?php

namespace App\Modules\Monitoring\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MonitoringSeeder - Creates sample metrics for testing
 */
class MonitoringSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'school_id' => 1,
                'metric_name' => 'active_users',
                'metric_type' => 'gauge',
                'value' => 45.00,
                'labels' => json_encode(['environment' => 'production', 'server' => 'web-01']),
                'recorded_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            ],
            [
                'school_id' => 1,
                'metric_name' => 'response_time_ms',
                'metric_type' => 'histogram',
                'value' => 125.50,
                'labels' => json_encode(['endpoint' => '/api/students', 'method' => 'GET']),
                'recorded_at' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
            ],
            [
                'school_id' => 1,
                'metric_name' => 'database_connections',
                'metric_type' => 'gauge',
                'value' => 12.00,
                'labels' => json_encode(['pool' => 'default', 'status' => 'active']),
                'recorded_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            ],
            [
                'school_id' => 1,
                'metric_name' => 'http_requests_total',
                'metric_type' => 'counter',
                'value' => 1523.00,
                'labels' => json_encode(['status' => '200', 'method' => 'GET']),
                'recorded_at' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
            ],
            [
                'school_id' => 1,
                'metric_name' => 'memory_usage_bytes',
                'metric_type' => 'gauge',
                'value' => 524288000.00,
                'labels' => json_encode(['server' => 'web-01', 'type' => 'heap']),
                'recorded_at' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
            ],
            [
                'school_id' => 1,
                'metric_name' => 'cache_hits',
                'metric_type' => 'counter',
                'value' => 856.00,
                'labels' => json_encode(['cache' => 'redis', 'region' => 'session']),
                'recorded_at' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
            ],
            [
                'school_id' => 1,
                'metric_name' => 'cpu_usage_percent',
                'metric_type' => 'gauge',
                'value' => 23.75,
                'labels' => json_encode(['server' => 'web-01', 'core' => 'all']),
                'recorded_at' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            ],
        ];

        $this->db->table('metrics')->insertBatch($data);
        
        echo "Created " . count($data) . " metric records.\n";
    }
}
