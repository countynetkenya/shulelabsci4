<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SchoolUserModel - Manages school-user relationships (multi-tenant access).
 */
class SchoolUserModel extends Model
{
    protected $table            = 'school_users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_id',
        'user_id',
        'role_id',
        'is_primary_school',
        'joined_at',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    // Validation
    protected $validationRules = [
        'school_id' => 'required|integer',
        'user_id'   => 'required|integer',
        'role_id'   => 'required|integer',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Get all schools for a user.
     */
    public function getUserSchools(int $userId): array
    {
        return $this->select('schools.*, school_users.role_id, school_users.is_primary_school, roles.role_name')
            ->join('schools', 'schools.id = school_users.school_id')
            ->join('roles', 'roles.id = school_users.role_id')
            ->where('school_users.user_id', $userId)
            ->where('schools.is_active', true)
            ->findAll();
    }

    /**
     * Get all users for a school.
     */
    public function getSchoolUsers(int $schoolId, ?int $roleId = null): array
    {
        $builder = $this->select('users.*, school_users.role_id, roles.role_name')
            ->join('users', 'users.id = school_users.user_id')
            ->join('roles', 'roles.id = school_users.role_id')
            ->where('school_users.school_id', $schoolId);

        if ($roleId) {
            $builder->where('school_users.role_id', $roleId);
        }

        return $builder->findAll();
    }

    /**
     * Assign user to school with role.
     */
    public function assignUserToSchool(int $userId, int $schoolId, int $roleId, bool $isPrimary = false): bool
    {
        $data = [
            'user_id'           => $userId,
            'school_id'         => $schoolId,
            'role_id'           => $roleId,
            'is_primary_school' => $isPrimary,
            'joined_at'         => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data) !== false;
    }

    /**
     * Remove user from school.
     */
    public function removeUserFromSchool(int $userId, int $schoolId): bool
    {
        return $this->where([
            'user_id'   => $userId,
            'school_id' => $schoolId,
        ])->delete();
    }

    /**
     * Update user's role in a school.
     */
    public function updateUserRole(int $userId, int $schoolId, int $newRoleId): bool
    {
        return $this->where([
            'user_id'   => $userId,
            'school_id' => $schoolId,
        ])->set(['role_id' => $newRoleId])->update();
    }

    /**
     * Check if user has access to school.
     */
    public function hasAccess(int $userId, int $schoolId): bool
    {
        $access = $this->where([
            'user_id'   => $userId,
            'school_id' => $schoolId,
        ])->first();

        return $access !== null;
    }

    /**
     * Get user's primary school.
     */
    public function getPrimarySchool(int $userId): ?array
    {
        return $this->select('schools.*')
            ->join('schools', 'schools.id = school_users.school_id')
            ->where('school_users.user_id', $userId)
            ->where('school_users.is_primary_school', true)
            ->first();
    }

    /**
     * Set user's primary school.
     */
    public function setPrimarySchool(int $userId, int $schoolId): bool
    {
        // Remove primary flag from all user's schools
        $this->where('user_id', $userId)->set(['is_primary_school' => false])->update();

        // Set new primary school
        return $this->where([
            'user_id'   => $userId,
            'school_id' => $schoolId,
        ])->set(['is_primary_school' => true])->update();
    }
}
