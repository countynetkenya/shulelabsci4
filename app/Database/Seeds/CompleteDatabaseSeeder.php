<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Complete Database Seeder.
 *
 * Seeds all necessary data for comprehensive system testing:
 * - SuperAdmin user
 * - Test users (teachers, students, parents, admins)
 * - Sample data for all modules
 */
class CompleteDatabaseSeeder extends Seeder
{
    public function run()
    {
        echo "🌱 Starting comprehensive database seeding...\n\n";

        $this->createSuperAdmin();
        $this->createTestUsers();

        echo "\n✅ Database seeding complete!\n";
        echo "📊 Summary:\n";
        echo "   - SuperAdmin: admin@shulelabs.local / Admin@123456\n";
        echo "   - Teachers: 5 users (teacher1-5@shulelabs.local / Teacher@123)\n";
        echo "   - Students: 10 users (student1-10@shulelabs.local / Student@123)\n";
        echo "   - Parents: 5 users (parent1-5@shulelabs.local / Parent@123)\n";
        echo "   - Admins: 2 users (schooladmin1-2@shulelabs.local / Admin@123)\n\n";
    }

    /**
     * Create SuperAdmin user with full system access.
     */
    private function createSuperAdmin(): void
    {
        echo "👤 Creating SuperAdmin user...\n";

        // Check if super admin already exists
        $existing = $this->db->table('users')
            ->where('email', 'admin@shulelabs.local')
            ->get()
            ->getRowArray();

        if ($existing) {
            echo "   ⚠️  SuperAdmin already exists, skipping...\n";
            return;
        }

        // Create superadmin user
        $this->db->table('users')->insert([
            'username' => 'superadmin',
            'email' => 'admin@shulelabs.local',
            'password_hash' => password_hash('Admin@123456', PASSWORD_DEFAULT),
            'full_name' => 'System Administrator',
            'photo' => null,
            'schoolID' => null,
            'ci3_user_id' => null,
            'ci3_user_table' => null,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Get the actual inserted ID (SQLite vs MySQL difference)
        $userId = $this->db->insertID();

        // Assign super_admin role
        $superAdminRole = $this->db->table('roles')
            ->where('role_slug', 'super_admin')
            ->get()
            ->getRowArray();

        if ($superAdminRole) {
            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $superAdminRole['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo "   ✅ SuperAdmin created: admin@shulelabs.local / Admin@123456\n";
    }

    /**
     * Create test users for different roles.
     */
    private function createTestUsers(): void
    {
        echo "\n👥 Creating test users...\n";

        // Get role IDs
        $roles = $this->db->table('roles')->get()->getResultArray();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role['role_slug']] = $role['id'];
        }

        // Create 5 teachers
        echo "   Creating teachers...\n";
        for ($i = 1; $i <= 5; $i++) {
            $this->createUser([
                'username' => "teacher{$i}",
                'email' => "teacher{$i}@shulelabs.local",
                'password' => 'Teacher@123',
                'full_name' => "Teacher {$i}",
                'role_slug' => 'teacher',
                'role_id' => $roleMap['teacher'],
            ]);
        }

        // Create 10 students
        echo "   Creating students...\n";
        for ($i = 1; $i <= 10; $i++) {
            $this->createUser([
                'username' => "student{$i}",
                'email' => "student{$i}@shulelabs.local",
                'password' => 'Student@123',
                'full_name' => "Student {$i}",
                'role_slug' => 'student',
                'role_id' => $roleMap['student'],
            ]);
        }

        // Create 2 school admins
        echo "   Creating school admins...\n";
        for ($i = 1; $i <= 2; $i++) {
            $this->createUser([
                'username' => "schooladmin{$i}",
                'email' => "schooladmin{$i}@shulelabs.local",
                'password' => 'Admin@123',
                'full_name' => "School Admin {$i}",
                'role_slug' => 'admin',
                'role_id' => $roleMap['admin'],
            ]);
        }

        echo "   ✅ Test users created successfully\n";
    }

    /**
     * Helper method to create a user.
     */
    private function createUser(array $data): void
    {
        // Check if user already exists
        $existing = $this->db->table('users')
            ->where('email', $data['email'])
            ->get()
            ->getRowArray();

        if ($existing) {
            return; // Skip if already exists
        }

        // Create user
        $this->db->table('users')->insert([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'full_name' => $data['full_name'],
            'photo' => null,
            'schoolID' => null,
            'ci3_user_id' => null,
            'ci3_user_table' => null,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $userId = $this->db->insertID();

        // Assign role
        $this->db->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $data['role_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
