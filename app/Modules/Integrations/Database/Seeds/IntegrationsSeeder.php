<?php

namespace App\Modules\Integrations\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * IntegrationsSeeder - Creates sample integration configurations for testing
 */
class IntegrationsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'M-Pesa Payment Gateway',
                'type' => 'payment',
                'adapter_class' => 'Modules\Integrations\Services\Adapters\Payment\MpesaAdapter',
                'config_json' => json_encode([
                    'consumer_key' => 'your_mpesa_consumer_key',
                    'consumer_secret' => 'your_mpesa_consumer_secret',
                    'shortcode' => '174379',
                    'passkey' => 'your_passkey',
                    'environment' => 'sandbox',
                ]),
                'is_active' => 1,
                'tenant_id' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Flutterwave Payment',
                'type' => 'payment',
                'adapter_class' => 'Modules\Integrations\Services\Adapters\Payment\FlutterwaveAdapter',
                'config_json' => json_encode([
                    'public_key' => 'FLWPUBK_TEST-xxxxx',
                    'secret_key' => 'FLWSECK_TEST-xxxxx',
                    'encryption_key' => 'FLWSECK_TEST-xxxxx',
                    'environment' => 'test',
                ]),
                'is_active' => 1,
                'tenant_id' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'SMS Provider - AfricasTalking',
                'type' => 'communication',
                'adapter_class' => 'Modules\Integrations\Services\Adapters\Communication\SmsAdapter',
                'config_json' => json_encode([
                    'api_key' => 'your_at_api_key',
                    'username' => 'your_at_username',
                    'sender_id' => 'SHULELABS',
                ]),
                'is_active' => 1,
                'tenant_id' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Google Drive Storage',
                'type' => 'storage',
                'adapter_class' => 'Modules\Integrations\Services\Adapters\Storage\GoogleDriveAdapter',
                'config_json' => json_encode([
                    'client_id' => 'your_client_id.apps.googleusercontent.com',
                    'client_secret' => 'your_client_secret',
                    'redirect_uri' => 'https://yourapp.com/oauth/callback',
                    'folder_id' => 'your_folder_id',
                ]),
                'is_active' => 0,
                'tenant_id' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Moodle LMS Integration',
                'type' => 'lms',
                'adapter_class' => 'Modules\Integrations\Services\Adapters\LMS\MoodleAdapter',
                'config_json' => json_encode([
                    'base_url' => 'https://moodle.yourschool.com',
                    'token' => 'your_moodle_webservice_token',
                    'service' => 'moodle_mobile_app',
                ]),
                'is_active' => 0,
                'tenant_id' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('integration_integrations')->insertBatch($data);
        
        echo "Created " . count($data) . " integration configuration records.\n";
    }
}
