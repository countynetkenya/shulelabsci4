<?php

namespace Modules\Mobile\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MobileSeeder - Seed sample mobile device records
 */
class MobileSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'user_id' => 1,
                'device_id' => 'device_ios_001',
                'device_name' => 'iPhone 13 Pro',
                'device_type' => 'ios',
                'os_version' => 'iOS 17.2',
                'app_version' => '2.1.0',
                'is_active' => 1,
                'last_active_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 1,
                'device_id' => 'device_android_002',
                'device_name' => 'Samsung Galaxy S23',
                'device_type' => 'android',
                'os_version' => 'Android 14',
                'app_version' => '2.1.0',
                'is_active' => 1,
                'last_active_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 week')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 1,
                'device_id' => 'device_web_003',
                'device_name' => 'Chrome on Windows',
                'device_type' => 'web',
                'os_version' => 'Windows 11',
                'app_version' => '2.0.5',
                'is_active' => 1,
                'last_active_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 1,
                'device_id' => 'device_ios_004',
                'device_name' => 'iPad Air',
                'device_type' => 'ios',
                'os_version' => 'iPadOS 17.1',
                'app_version' => '2.0.8',
                'is_active' => 0,
                'last_active_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 month')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 1,
                'device_id' => 'device_android_005',
                'device_name' => 'Google Pixel 8',
                'device_type' => 'android',
                'os_version' => 'Android 14',
                'app_version' => '2.1.0',
                'is_active' => 1,
                'last_active_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('mobile_devices')->insertBatch($data);
    }
}
