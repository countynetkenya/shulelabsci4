<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MultiSchoolUserSeeder - Create 195+ users across 5 schools.
 *
 * Distribution:
 * - School 1 (Nairobi Primary): 40 users
 * - School 2 (Mombasa Secondary): 50 users
 * - School 3 (Kisumu Mixed): 35 users
 * - School 4 (Eldoret Technical): 45 users
 * - School 5 (Nakuru Kids): 25 users
 * Total: 195 users
 */
class MultiSchoolUserSeeder extends Seeder
{
    public function run()
    {
        // Call school seeder first
        $this->call('MultiSchoolSeeder');

        $userCounter = 100; // Start from ID 100 to avoid conflicts

        // Get role IDs
        $roles = $this->getRoleIds();

        // School 1: Nairobi Primary School (40 users)
        $this->createSchoolUsers(1, 'Nairobi Primary', [
            'school_admins' => 1,
            'teachers'      => 8,
            'students'      => 25,
            'parents'       => 5,
            'librarians'    => 1,
        ], $roles, $userCounter);

        // School 2: Mombasa Secondary School (50 users)
        $this->createSchoolUsers(2, 'Mombasa Secondary', [
            'school_admins' => 1,
            'teachers'      => 12,
            'students'      => 30,
            'parents'       => 5,
            'accountants'   => 1,
            'librarians'    => 1,
        ], $roles, $userCounter);

        // School 3: Kisumu Mixed Academy (35 users)
        $this->createSchoolUsers(3, 'Kisumu Mixed', [
            'school_admins' => 1,
            'teachers'      => 10,
            'students'      => 20,
            'parents'       => 3,
            'receptionists' => 1,
        ], $roles, $userCounter);

        // School 4: Eldoret Technical College (45 users)
        $this->createSchoolUsers(4, 'Eldoret Technical', [
            'school_admins' => 2,
            'teachers'      => 15,
            'students'      => 25,
            'parents'       => 2,
            'accountants'   => 1,
        ], $roles, $userCounter);

        // School 5: Nakuru Kids School (25 users)
        $this->createSchoolUsers(5, 'Nakuru Kids', [
            'school_admins' => 1,
            'teachers'      => 5,
            'students'      => 15,
            'parents'       => 4,
        ], $roles, $userCounter);

        echo "\n✅ Created 195 users across 5 schools\n";
    }

    private function getRoleIds(): array
    {
        $db = \Config\Database::connect();

        return [
            'superadmin'   => $db->table('roles')->where('role_name', 'Super Admin')->get()->getRow()->id ?? 1,
            'admin'        => $db->table('roles')->where('role_name', 'Admin')->get()->getRow()->id ?? 2,
            'teacher'      => $db->table('roles')->where('role_name', 'Teacher')->get()->getRow()->id ?? 3,
            'student'      => $db->table('roles')->where('role_name', 'Student')->get()->getRow()->id ?? 4,
            'parent'       => $db->table('roles')->where('role_name', 'Parent')->get()->getRow()->id ?? 5,
            'accountant'   => $db->table('roles')->where('role_name', 'Accountant')->get()->getRow()->id ?? 6,
            'librarian'    => $db->table('roles')->where('role_name', 'Librarian')->get()->getRow()->id ?? 7,
            'receptionist' => $db->table('roles')->where('role_name', 'Receptionist')->get()->getRow()->id ?? 8,
        ];
    }

    private function createSchoolUsers(int $schoolId, string $schoolName, array $counts, array $roles, int &$userCounter): void
    {
        $db = \Config\Database::connect();

        echo "\n📚 Creating users for {$schoolName}...\n";

        // Create School Admins
        if (isset($counts['school_admins'])) {
            for ($i = 1; $i <= $counts['school_admins']; $i++) {
                $userId = $this->createUser($db, [
                    'username'      => "schooladmin{$userCounter}",
                    'email'         => "schooladmin{$userCounter}@school{$schoolId}.local",
                    'password_hash' => password_hash('Admin@123', PASSWORD_BCRYPT),
                    'full_name'     => "Admin School{$schoolId}_{$i}",
                ]);

                $this->assignToSchool($db, $userId, $schoolId, $roles['admin'], $i === 1);
                $userCounter++;
            }

            echo "   ✓ {$counts['school_admins']} school admin(s)\n";
        }

        // Create Teachers
        if (isset($counts['teachers'])) {
            for ($i = 1; $i <= $counts['teachers']; $i++) {
                $userId = $this->createUser($db, [
                    'username'      => "teacher{$userCounter}",
                    'email'         => "teacher{$userCounter}@school{$schoolId}.local",
                    'password_hash' => password_hash('Teacher@123', PASSWORD_BCRYPT),
                    'full_name'     => "Teacher T{$userCounter}",
                ]);

                $this->assignToSchool($db, $userId, $schoolId, $roles['teacher']);
                $userCounter++;
            }

            echo "   ✓ {$counts['teachers']} teacher(s)\n";
        }

        // Create Students
        if (isset($counts['students'])) {
            for ($i = 1; $i <= $counts['students']; $i++) {
                $userId = $this->createUser($db, [
                    'username'      => "student{$userCounter}",
                    'email'         => "student{$userCounter}@school{$schoolId}.local",
                    'password_hash' => password_hash('Student@123', PASSWORD_BCRYPT),
                    'full_name'     => "Student S{$userCounter}",
                ]);

                $this->assignToSchool($db, $userId, $schoolId, $roles['student']);
                $userCounter++;
            }

            echo "   ✓ {$counts['students']} student(s)\n";
        }

        // Create Parents
        if (isset($counts['parents'])) {
            for ($i = 1; $i <= $counts['parents']; $i++) {
                $userId = $this->createUser($db, [
                    'username'      => "parent{$userCounter}",
                    'email'         => "parent{$userCounter}@school{$schoolId}.local",
                    'password_hash' => password_hash('Parent@123', PASSWORD_BCRYPT),
                    'full_name'     => "Parent P{$userCounter}",
                ]);

                $this->assignToSchool($db, $userId, $schoolId, $roles['parent']);
                $userCounter++;
            }

            echo "   ✓ {$counts['parents']} parent(s)\n";
        }

        // Create Accountants
        if (isset($counts['accountants'])) {
            for ($i = 1; $i <= $counts['accountants']; $i++) {
                $userId = $this->createUser($db, [
                    'username'      => "accountant{$userCounter}",
                    'email'         => "accountant{$userCounter}@school{$schoolId}.local",
                    'password_hash' => password_hash('Accountant@123', PASSWORD_BCRYPT),
                    'full_name'     => "Accountant A{$userCounter}",
                ]);

                $this->assignToSchool($db, $userId, $schoolId, $roles['accountant']);
                $userCounter++;
            }

            echo "   ✓ {$counts['accountants']} accountant(s)\n";
        }

        // Create Librarians
        if (isset($counts['librarians'])) {
            for ($i = 1; $i <= $counts['librarians']; $i++) {
                $userId = $this->createUser($db, [
                    'username'      => "librarian{$userCounter}",
                    'email'         => "librarian{$userCounter}@school{$schoolId}.local",
                    'password_hash' => password_hash('Librarian@123', PASSWORD_BCRYPT),
                    'full_name'     => "Librarian L{$userCounter}",
                ]);

                $this->assignToSchool($db, $userId, $schoolId, $roles['librarian']);
                $userCounter++;
            }

            echo "   ✓ {$counts['librarians']} librarian(s)\n";
        }

        // Create Receptionists
        if (isset($counts['receptionists'])) {
            for ($i = 1; $i <= $counts['receptionists']; $i++) {
                $userId = $this->createUser($db, [
                    'username'      => "receptionist{$userCounter}",
                    'email'         => "receptionist{$userCounter}@school{$schoolId}.local",
                    'password_hash' => password_hash('Receptionist@123', PASSWORD_BCRYPT),
                    'full_name'     => "Receptionist R{$userCounter}",
                ]);

                $this->assignToSchool($db, $userId, $schoolId, $roles['receptionist']);
                $userCounter++;
            }

            echo "   ✓ {$counts['receptionists']} receptionist(s)\n";
        }
    }

    private function createUser($db, array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = 1;

        $db->table('users')->insert($data);

        return $db->insertID();
    }

    private function assignToSchool($db, int $userId, int $schoolId, int $roleId, bool $isPrimary = false): void
    {
        $db->table('school_users')->insert([
            'user_id'           => $userId,
            'school_id'         => $schoolId,
            'role_id'           => $roleId,
            'is_primary_school' => $isPrimary,
            'joined_at'         => date('Y-m-d H:i:s'),
        ]);
    }
}
