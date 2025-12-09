<?php

namespace App\Modules\Foundation\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * FoundationSeeder - Populates core foundation settings.
 *
 * Seeds essential configuration for the Foundation module including
 * general settings, mail configuration, payment settings, and security settings.
 */
class FoundationSeeder extends Seeder
{
    public function run()
    {
        // Settings data (using the global settings table)
        $settings = [
            // General/Platform Settings
            [
                'class'   => 'general',
                'key'     => 'platform_name',
                'value'   => 'ShuleLabs',
                'type'    => 'string',
                'context' => 'system',
            ],
            [
                'class'   => 'general',
                'key'     => 'support_email',
                'value'   => 'support@shulelabs.com',
                'type'    => 'string',
                'context' => 'system',
            ],
            [
                'class'   => 'general',
                'key'     => 'platform_url',
                'value'   => 'https://shulelabs.com',
                'type'    => 'string',
                'context' => 'system',
            ],

            // Mail Settings
            [
                'class'   => 'mail',
                'key'     => 'host',
                'value'   => 'smtp.example.com',
                'type'    => 'string',
                'context' => 'system',
            ],
            [
                'class'   => 'mail',
                'key'     => 'port',
                'value'   => '587',
                'type'    => 'integer',
                'context' => 'system',
            ],
            [
                'class'   => 'mail',
                'key'     => 'username',
                'value'   => '',
                'type'    => 'string',
                'context' => 'system',
            ],
            [
                'class'   => 'mail',
                'key'     => 'password',
                'value'   => '',
                'type'    => 'string',
                'context' => 'system',
            ],
            [
                'class'   => 'mail',
                'key'     => 'encryption',
                'value'   => 'tls',
                'type'    => 'string',
                'context' => 'system',
            ],

            // Payment Gateway Settings
            [
                'class'   => 'payment',
                'key'     => 'pesapal_key',
                'value'   => '',
                'type'    => 'string',
                'context' => 'system',
            ],
            [
                'class'   => 'payment',
                'key'     => 'pesapal_secret',
                'value'   => '',
                'type'    => 'string',
                'context' => 'system',
            ],
            [
                'class'   => 'payment',
                'key'     => 'default_currency',
                'value'   => 'KES',
                'type'    => 'string',
                'context' => 'app',
            ],

            // Security Settings
            [
                'class'   => 'security',
                'key'     => 'require_email_verification',
                'value'   => 'true',
                'type'    => 'boolean',
                'context' => 'system',
            ],
            [
                'class'   => 'security',
                'key'     => 'max_login_attempts',
                'value'   => '5',
                'type'    => 'integer',
                'context' => 'system',
            ],
            [
                'class'   => 'security',
                'key'     => 'lockout_duration',
                'value'   => '900',
                'type'    => 'integer',
                'context' => 'system',
            ],
        ];

        foreach ($settings as $setting) {
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

        echo "Foundation settings seeded successfully.\n";
    }
}
