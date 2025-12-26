<?php

namespace Modules\Security\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * SecuritySeeder - Populates sample security log data.
 */
class SecuritySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'identifier'      => 'admin@example.com',
                'ip_address'      => '192.168.1.10',
                'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
                'attempt_type'    => 'login',
                'was_successful'  => 1,
                'failure_reason'  => null,
                'created_at'      => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
            [
                'identifier'      => 'teacher@example.com',
                'ip_address'      => '192.168.1.25',
                'user_agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X) Safari/17.0',
                'attempt_type'    => 'login',
                'was_successful'  => 1,
                'failure_reason'  => null,
                'created_at'      => date('Y-m-d H:i:s', strtotime('-1 hour')),
            ],
            [
                'identifier'      => 'unknown@test.com',
                'ip_address'      => '203.0.113.45',
                'user_agent'      => 'Mozilla/5.0 (X11; Linux x86_64) Firefox/121.0',
                'attempt_type'    => 'login',
                'was_successful'  => 0,
                'failure_reason'  => 'Invalid credentials',
                'created_at'      => date('Y-m-d H:i:s', strtotime('-45 minutes')),
            ],
            [
                'identifier'      => 'admin@example.com',
                'ip_address'      => '192.168.1.10',
                'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
                'attempt_type'    => '2fa',
                'was_successful'  => 1,
                'failure_reason'  => null,
                'created_at'      => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            ],
            [
                'identifier'      => 'hacker@evil.com',
                'ip_address'      => '198.51.100.78',
                'user_agent'      => 'curl/7.68.0',
                'attempt_type'    => 'login',
                'was_successful'  => 0,
                'failure_reason'  => 'Account locked',
                'created_at'      => date('Y-m-d H:i:s', strtotime('-15 minutes')),
            ],
            [
                'identifier'      => 'student@example.com',
                'ip_address'      => '192.168.1.50',
                'user_agent'      => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Mobile Safari/605.1.15',
                'attempt_type'    => 'login',
                'was_successful'  => 1,
                'failure_reason'  => null,
                'created_at'      => date('Y-m-d H:i:s', strtotime('-5 minutes')),
            ],
            [
                'identifier'      => 'parent@example.com',
                'ip_address'      => '192.168.1.65',
                'user_agent'      => 'Mozilla/5.0 (Android 14) Mobile Safari/537.36',
                'attempt_type'    => 'password_reset',
                'was_successful'  => 1,
                'failure_reason'  => null,
                'created_at'      => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            ],
        ];

        $this->db->table('login_attempts')->insertBatch($data);

        echo 'Inserted ' . count($data) . " sample security logs.\n";
    }
}
