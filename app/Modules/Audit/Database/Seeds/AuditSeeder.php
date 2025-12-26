<?php

namespace App\Modules\Audit\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * AuditSeeder - Creates sample audit events for testing.
 */
class AuditSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'school_id' => 1,
                'user_id' => 1,
                'event_key' => 'login_' . uniqid(),
                'event_type' => 'login',
                'entity_type' => 'user',
                'entity_id' => 1,
                'before_state' => null,
                'after_state' => json_encode(['status' => 'logged_in']),
                'changed_fields' => null,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'request_uri' => '/auth/signin',
                'trace_id' => 'trace_' . uniqid(),
                'previous_hash' => null,
                'hash_value' => hash('sha256', 'login_event_1'),
                'metadata_json' => json_encode(['device' => 'desktop']),
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
            [
                'school_id' => 1,
                'user_id' => 1,
                'event_key' => 'create_' . uniqid(),
                'event_type' => 'create',
                'entity_type' => 'student',
                'entity_id' => 123,
                'before_state' => null,
                'after_state' => json_encode(['name' => 'John Doe', 'grade' => '10']),
                'changed_fields' => json_encode(['name', 'grade']),
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'request_uri' => '/students/store',
                'trace_id' => 'trace_' . uniqid(),
                'previous_hash' => hash('sha256', 'login_event_1'),
                'hash_value' => hash('sha256', 'create_event_2'),
                'metadata_json' => json_encode(['automated' => false]),
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            ],
            [
                'school_id' => 1,
                'user_id' => 2,
                'event_key' => 'update_' . uniqid(),
                'event_type' => 'update',
                'entity_type' => 'book',
                'entity_id' => 45,
                'before_state' => json_encode(['available_copies' => 5]),
                'after_state' => json_encode(['available_copies' => 4]),
                'changed_fields' => json_encode(['available_copies']),
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'request_uri' => '/library/update/45',
                'trace_id' => 'trace_' . uniqid(),
                'previous_hash' => hash('sha256', 'create_event_2'),
                'hash_value' => hash('sha256', 'update_event_3'),
                'metadata_json' => json_encode(['reason' => 'book_borrowed']),
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            ],
            [
                'school_id' => 1,
                'user_id' => 1,
                'event_key' => 'delete_' . uniqid(),
                'event_type' => 'delete',
                'entity_type' => 'inventory',
                'entity_id' => 78,
                'before_state' => json_encode(['name' => 'Old Laptop', 'quantity' => 1]),
                'after_state' => null,
                'changed_fields' => json_encode(['deleted_at']),
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'request_uri' => '/inventory/delete/78',
                'trace_id' => 'trace_' . uniqid(),
                'previous_hash' => hash('sha256', 'update_event_3'),
                'hash_value' => hash('sha256', 'delete_event_4'),
                'metadata_json' => json_encode(['reason' => 'damaged_beyond_repair']),
                'created_at' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
            ],
            [
                'school_id' => 1,
                'user_id' => 3,
                'event_key' => 'access_' . uniqid(),
                'event_type' => 'access',
                'entity_type' => 'report',
                'entity_id' => 15,
                'before_state' => null,
                'after_state' => null,
                'changed_fields' => null,
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'request_uri' => '/reports/view/15',
                'trace_id' => 'trace_' . uniqid(),
                'previous_hash' => hash('sha256', 'delete_event_4'),
                'hash_value' => hash('sha256', 'access_event_5'),
                'metadata_json' => json_encode(['report_type' => 'financial']),
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
            ],
        ];

        $this->db->table('audit_events')->insertBatch($data);

        echo 'Created ' . count($data) . " audit event records.\n";
    }
}
