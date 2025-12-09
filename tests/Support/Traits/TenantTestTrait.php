<?php

namespace Tests\Support\Traits;

/**
 * TenantTestTrait - Standardized tenant context setup for feature tests
 * 
 * This trait provides a consistent way to set up school, user, role, and session
 * data for multi-tenant tests. It ensures IDs match between database records and
 * session state, preventing authentication and tenant filter issues.
 * 
 * Usage:
 * 1. Add `use TenantTestTrait;` to your test class
 * 2. Call `$this->setupTenantContext()` in `setUp()`
 * 3. Use `$this->withSession($this->getAdminSession())` for authenticated requests
 * 
 * Available properties after setup:
 * - $this->schoolId - The test school ID
 * - $this->userId - The test admin user ID
 * - $this->roleId - The admin role ID
 */
trait TenantTestTrait
{
    protected int $schoolId;
    protected int $userId;
    protected int $roleId;

    /**
     * Set up standardized tenant context for testing
     * Creates a test school, admin role, and admin user with matching IDs
     */
    protected function setupTenantContext(): void
    {
        // Create test school
        $this->db->table('schools')->insert([
            'school_name'   => 'Test School',
            'school_code'   => 'TEST001',
            'email'         => 'test@school.com',
            'phone'         => '0700000000',
            'address'       => '123 Test Street',
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->schoolId = (int) $this->db->insertID();

        // Create admin role
        $this->db->table('roles')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Admin',
            'description' => 'Administrator role for testing',
            'permissions' => json_encode(['*']),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->roleId = (int) $this->db->insertID();

        // Create admin user
        $this->db->table('users')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Admin User',
            'email'       => 'admin@test.com',
            'username'    => 'admin',
            'password'    => password_hash('password', PASSWORD_DEFAULT),
            'role_id'     => $this->roleId,
            'usertypeID'  => 1, // Admin usertype
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->userId = (int) $this->db->insertID();
    }

    /**
     * Get admin session data for authenticated requests
     * 
     * @return array Session data matching the user created in setupTenantContext()
     */
    protected function getAdminSession(): array
    {
        return [
            'user_id'    => $this->userId,
            'school_id'  => $this->schoolId,
            'schoolID'   => $this->schoolId, // Legacy key
            'role_id'    => $this->roleId,
            'usertypeID' => 1, // Admin
            'name'       => 'Admin User',
            'email'      => 'admin@test.com',
            'username'   => 'admin',
            'is_admin'   => true,
            'loggedIn'   => true,
        ];
    }

    /**
     * Get teacher session data for teacher-specific tests
     * Creates a teacher user if needed
     * 
     * @return array Session data for a teacher user
     */
    protected function getTeacherSession(): array
    {
        // Check if teacher user exists, create if not
        $teacher = $this->db->table('users')
            ->where('school_id', $this->schoolId)
            ->where('usertypeID', 2)
            ->get()
            ->getRowArray();

        if (!$teacher) {
            $this->db->table('users')->insert([
                'school_id'   => $this->schoolId,
                'name'        => 'Teacher User',
                'email'       => 'teacher@test.com',
                'username'    => 'teacher',
                'password'    => password_hash('password', PASSWORD_DEFAULT),
                'role_id'     => $this->roleId,
                'usertypeID'  => 2, // Teacher usertype
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $teacherId = (int) $this->db->insertID();
        } else {
            $teacherId = (int) $teacher['id'];
        }

        return [
            'user_id'    => $teacherId,
            'school_id'  => $this->schoolId,
            'schoolID'   => $this->schoolId,
            'role_id'    => $this->roleId,
            'usertypeID' => 2, // Teacher
            'name'       => 'Teacher User',
            'email'      => 'teacher@test.com',
            'username'   => 'teacher',
            'is_admin'   => false,
            'loggedIn'   => true,
        ];
    }

    /**
     * Get student session data for student-specific tests
     * Creates a student user if needed
     * 
     * @return array Session data for a student user
     */
    protected function getStudentSession(): array
    {
        // Check if student user exists, create if not
        $student = $this->db->table('users')
            ->where('school_id', $this->schoolId)
            ->where('usertypeID', 3)
            ->get()
            ->getRowArray();

        if (!$student) {
            $this->db->table('users')->insert([
                'school_id'   => $this->schoolId,
                'name'        => 'Student User',
                'email'       => 'student@test.com',
                'username'    => 'student',
                'password'    => password_hash('password', PASSWORD_DEFAULT),
                'role_id'     => $this->roleId,
                'usertypeID'  => 3, // Student usertype
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $studentId = (int) $this->db->insertID();
        } else {
            $studentId = (int) $student['id'];
        }

        return [
            'user_id'    => $studentId,
            'school_id'  => $this->schoolId,
            'schoolID'   => $this->schoolId,
            'role_id'    => $this->roleId,
            'usertypeID' => 3, // Student
            'name'       => 'Student User',
            'email'      => 'student@test.com',
            'username'   => 'student',
            'is_admin'   => false,
            'loggedIn'   => true,
        ];
    }
}
