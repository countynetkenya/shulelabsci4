<?php

namespace App\Modules\Admin\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * AdminSeeder - Populates sample system settings.
 */
class AdminSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Mail settings
            [
                'class'   => 'mail',
                'key'     => 'smtp_host',
                'value'   => 'smtp.example.com',
                'type'    => 'string',
                'context' => 'system',
            ],
            [
                'class'   => 'mail',
                'key'     => 'smtp_port',
                'value'   => '587',
                'type'    => 'integer',
                'context' => 'system',
            ],
            [
                'class'   => 'mail',
                'key'     => 'smtp_user',
                'value'   => 'noreply@shulelabs.com',
                'type'    => 'string',
                'context' => 'system',
            ],

            // Payment settings
            [
                'class'   => 'payment',
                'key'     => 'mpesa_enabled',
                'value'   => 'true',
                'type'    => 'boolean',
                'context' => 'app',
            ],
            [
                'class'   => 'payment',
                'key'     => 'mpesa_shortcode',
                'value'   => '174379',
                'type'    => 'string',
                'context' => 'app',
            ],
            [
                'class'   => 'payment',
                'key'     => 'currency',
                'value'   => 'KES',
                'type'    => 'string',
                'context' => 'app',
            ],

            // General settings
            [
                'class'   => 'general',
                'key'     => 'app_name',
                'value'   => 'ShuleLabs',
                'type'    => 'string',
                'context' => 'app',
            ],
            [
                'class'   => 'general',
                'key'     => 'timezone',
                'value'   => 'Africa/Nairobi',
                'type'    => 'string',
                'context' => 'app',
            ],
            [
                'class'   => 'general',
                'key'     => 'date_format',
                'value'   => 'Y-m-d',
                'type'    => 'string',
                'context' => 'app',
            ],
            [
                'class'   => 'general',
                'key'     => 'pagination_limit',
                'value'   => '20',
                'type'    => 'integer',
                'context' => 'app',
            ],

            // Security settings
            [
                'class'   => 'security',
                'key'     => 'session_timeout',
                'value'   => '3600',
                'type'    => 'integer',
                'context' => 'system',
            ],
            [
                'class'   => 'security',
                'key'     => 'password_min_length',
                'value'   => '8',
                'type'    => 'integer',
                'context' => 'system',
            ],
            [
                'class'   => 'security',
                'key'     => 'enable_2fa',
                'value'   => 'false',
                'type'    => 'boolean',
                'context' => 'system',
            ],
        ];

        foreach ($data as $setting) {
            // Check if setting already exists
            $existing = $this->db->table('settings')
                ->where('class', $setting['class'])
                ->where('key', $setting['key'])
                ->get()
                ->getRowArray();

            if (!$existing) {
                $this->db->table('settings')->insert($setting);
            }
        }

        echo "Admin settings seeded successfully.\n";
    }
}
