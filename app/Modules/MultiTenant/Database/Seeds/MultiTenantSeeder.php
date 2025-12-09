<?php

namespace App\Modules\MultiTenant\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MultiTenantSeeder - Populates sample tenant data
 */
class MultiTenantSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'uuid'             => $this->generateUuid(),
                'name'             => 'Nairobi High School',
                'subdomain'        => 'nairobi-high',
                'custom_domain'    => null,
                'status'           => 'active',
                'tier'             => 'professional',
                'settings'         => json_encode(['theme' => 'blue', 'language' => 'en']),
                'features'         => json_encode(['sms' => true, 'email' => true, 'reports' => true]),
                'storage_quota_mb' => 10000,
                'storage_used_mb'  => 2500,
                'student_quota'    => 1000,
                'staff_quota'      => 50,
                'trial_ends_at'    => null,
                'activated_at'     => date('Y-m-d H:i:s', strtotime('-6 months')),
                'suspended_at'     => null,
                'cancelled_at'     => null,
                'created_at'       => date('Y-m-d H:i:s', strtotime('-6 months')),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'uuid'             => $this->generateUuid(),
                'name'             => 'Mombasa Academy',
                'subdomain'        => 'mombasa-academy',
                'custom_domain'    => 'mombasa-academy.ac.ke',
                'status'           => 'active',
                'tier'             => 'enterprise',
                'settings'         => json_encode(['theme' => 'green', 'language' => 'en']),
                'features'         => json_encode(['sms' => true, 'email' => true, 'reports' => true, 'api' => true]),
                'storage_quota_mb' => 50000,
                'storage_used_mb'  => 15000,
                'student_quota'    => null, // Unlimited
                'staff_quota'      => null, // Unlimited
                'trial_ends_at'    => null,
                'activated_at'     => date('Y-m-d H:i:s', strtotime('-1 year')),
                'suspended_at'     => null,
                'cancelled_at'     => null,
                'created_at'       => date('Y-m-d H:i:s', strtotime('-1 year')),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'uuid'             => $this->generateUuid(),
                'name'             => 'Kisumu Junior School',
                'subdomain'        => 'kisumu-junior',
                'custom_domain'    => null,
                'status'           => 'active',
                'tier'             => 'starter',
                'settings'         => json_encode(['theme' => 'orange', 'language' => 'en']),
                'features'         => json_encode(['sms' => false, 'email' => true, 'reports' => true]),
                'storage_quota_mb' => 5000,
                'storage_used_mb'  => 1200,
                'student_quota'    => 500,
                'staff_quota'      => 25,
                'trial_ends_at'    => null,
                'activated_at'     => date('Y-m-d H:i:s', strtotime('-3 months')),
                'suspended_at'     => null,
                'cancelled_at'     => null,
                'created_at'       => date('Y-m-d H:i:s', strtotime('-3 months')),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'uuid'             => $this->generateUuid(),
                'name'             => 'Nakuru Preparatory',
                'subdomain'        => 'nakuru-prep',
                'custom_domain'    => null,
                'status'           => 'pending',
                'tier'             => 'free',
                'settings'         => json_encode(['theme' => 'default', 'language' => 'en']),
                'features'         => json_encode(['sms' => false, 'email' => false, 'reports' => true]),
                'storage_quota_mb' => 2000,
                'storage_used_mb'  => 0,
                'student_quota'    => 100,
                'staff_quota'      => 10,
                'trial_ends_at'    => date('Y-m-d', strtotime('+30 days')),
                'activated_at'     => null,
                'suspended_at'     => null,
                'cancelled_at'     => null,
                'created_at'       => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'uuid'             => $this->generateUuid(),
                'name'             => 'Eldoret College',
                'subdomain'        => 'eldoret-college',
                'custom_domain'    => null,
                'status'           => 'suspended',
                'tier'             => 'professional',
                'settings'         => json_encode(['theme' => 'red', 'language' => 'en']),
                'features'         => json_encode(['sms' => true, 'email' => true, 'reports' => true]),
                'storage_quota_mb' => 10000,
                'storage_used_mb'  => 8500,
                'student_quota'    => 800,
                'staff_quota'      => 40,
                'trial_ends_at'    => null,
                'activated_at'     => date('Y-m-d H:i:s', strtotime('-8 months')),
                'suspended_at'     => date('Y-m-d H:i:s', strtotime('-1 week')),
                'cancelled_at'     => null,
                'created_at'       => date('Y-m-d H:i:s', strtotime('-8 months')),
                'updated_at'       => date('Y-m-d H:i:s', strtotime('-1 week')),
            ],
        ];

        foreach ($data as $tenant) {
            // Check if tenant already exists
            $existing = $this->db->table('tenants')
                ->where('subdomain', $tenant['subdomain'])
                ->get()
                ->getRowArray();

            if (!$existing) {
                $this->db->table('tenants')->insert($tenant);
            }
        }

        echo "MultiTenant data seeded successfully.\n";
    }

    /**
     * Generate a v4 UUID
     */
    protected function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
