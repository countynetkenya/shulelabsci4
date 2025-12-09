<?php

namespace App\Modules\Wallets\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run()
    {
        $schoolId = 1; // Default test school ID

        // Create 5 wallets with different types and statuses
        $wallets = [
            [
                'school_id'   => $schoolId,
                'user_id'     => 1,
                'wallet_type' => 'student',
                'balance'     => 5000.00,
                'currency'    => 'KES',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => $schoolId,
                'user_id'     => 2,
                'wallet_type' => 'student',
                'balance'     => 12500.50,
                'currency'    => 'KES',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => $schoolId,
                'user_id'     => 3,
                'wallet_type' => 'parent',
                'balance'     => 25000.00,
                'currency'    => 'KES',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => $schoolId,
                'user_id'     => 4,
                'wallet_type' => 'staff',
                'balance'     => 8750.25,
                'currency'    => 'KES',
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'school_id'   => $schoolId,
                'user_id'     => 5,
                'wallet_type' => 'student',
                'balance'     => 0.00,
                'currency'    => 'KES',
                'status'      => 'suspended',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('wallets')->insertBatch($wallets);
    }
}
