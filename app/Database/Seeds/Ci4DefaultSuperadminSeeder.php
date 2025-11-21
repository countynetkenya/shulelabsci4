<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Libraries\HashCompat;

/**
 * CI4 Default Superadmin Seeder
 * 
 * Creates a default CI4-native superadmin account for fresh installations
 * or when CI3 tables are empty/broken.
 * 
 * Default Credentials:
 * - Username: admin_ci4
 * - Password: ChangeMe123!
 * 
 * IMPORTANT: Change the password immediately after first login!
 */
class Ci4DefaultSuperadminSeeder extends Seeder
{
    public function run()
    {
        // Check if a superadmin already exists
        $existingSuperadmin = $this->db->table('ci4_user_roles ur')
            ->join('ci4_roles r', 'r.id = ur.role_id')
            ->where('r.role_slug', 'super_admin')
            ->countAllResults();

        if ($existingSuperadmin > 0) {
            echo "A superadmin already exists. Skipping seeder.\n";
            return;
        }

        echo "Creating default CI4 superadmin...\n";

        // Default credentials
        $username = 'admin_ci4';
        $password = 'ChangeMe123!';
        $email = 'admin@shulelabs.local';

        // Hash password using CI3-compatible method
        $hashCompat = new HashCompat();
        $passwordHash = $hashCompat->hash($password);

        // Insert user
        $userData = [
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'full_name' => 'CI4 System Administrator',
            'photo' => 'default.png',
            'schoolID' => '0', // Super admin has access to all schools
            'ci3_user_id' => null,
            'ci3_user_table' => null,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('ci4_users')->insert($userData);
        $userId = $this->db->insertID();

        // Get super_admin role
        $superAdminRole = $this->db->table('ci4_roles')
            ->where('role_slug', 'super_admin')
            ->get()
            ->getRow();

        if ($superAdminRole) {
            // Assign super_admin role
            $this->db->table('ci4_user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $superAdminRole->id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            echo "✓ Default CI4 superadmin created successfully!\n";
            echo "  Username: {$username}\n";
            echo "  Password: {$password}\n";
            echo "  Email: {$email}\n";
            echo "\n";
            echo "⚠️  IMPORTANT: Please change the password immediately after first login!\n";
        } else {
            echo "✗ Error: super_admin role not found. Please run migrations first.\n";
        }
    }
}
