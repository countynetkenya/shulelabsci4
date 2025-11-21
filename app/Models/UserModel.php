<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User Model
 *
 * Handles user authentication using CI4-native user tables
 * Uses ci4_users table exclusively for authentication
 */
class UserModel extends Model
{
    protected $table = 'ci4_users';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'username',
        'email',
        'password_hash',
        'full_name',
        'photo',
        'schoolID',
        'ci3_user_id',
        'ci3_user_table',
        'is_active',
    ];

    /**
     * Find user by username (active users only)
     *
     * @param string $username
     * @return object|null
     */
    public function findByUsername(string $username): ?object
    {
        return $this->where('username', $username)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Find user by username regardless of active status
     * Used for detailed signin error messages
     *
     * @param string $username
     * @return object|null
     */
    public function findByUsernameAnyStatus(string $username): ?object
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Get user with their roles
     *
     * @param int $userId
     * @return object|null User object with roles array attached
     */
    public function getUserWithRoles(int $userId): ?object
    {
        // Get user
        $user = $this->find($userId);
        
        if (!$user) {
            return null;
        }

        // Get roles
        $roles = $this->db->table('ci4_user_roles ur')
            ->select('r.id, r.role_name, r.role_slug, r.ci3_usertype_id, r.description')
            ->join('ci4_roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->getResult();

        $user->roles = $roles;

        // For backward compatibility, set usertypeID from primary role
        if (!empty($roles)) {
            $user->usertypeID = $roles[0]->ci3_usertype_id;
        }

        return $user;
    }

    /**
     * Get user's primary role (first assigned role)
     *
     * @param int $userId
     * @return object|null
     */
    public function getUserPrimaryRole(int $userId): ?object
    {
        return $this->db->table('ci4_user_roles ur')
            ->select('r.*')
            ->join('ci4_roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->orderBy('ur.id', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();
    }

    /**
     * Check if user has a specific role
     *
     * @param int $userId
     * @param string $roleSlug
     * @return bool
     */
    public function hasRole(int $userId, string $roleSlug): bool
    {
        $count = $this->db->table('ci4_user_roles ur')
            ->join('ci4_roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->where('r.role_slug', $roleSlug)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Legacy method: Get user for signin (backward compatibility)
     * Now uses ci4_users table instead of multiple CI3 tables
     *
     * @param string $username
     * @param string $hashedPassword
     * @return object|null
     * @deprecated Use findByUsername() and verify password separately
     */
    public function getUserForSignin(string $username, string $hashedPassword): ?object
    {
        $user = $this->where('username', $username)
            ->where('password_hash', $hashedPassword)
            ->where('is_active', 1)
            ->first();

        if ($user) {
            // Add legacy fields for backward compatibility
            $user->userID = $user->id;
            $user->name = $user->full_name;
            $user->active = $user->is_active;
            $user->user_table = 'ci4_users';

            // Get primary role to set usertypeID
            $role = $this->getUserPrimaryRole($user->id);
            if ($role) {
                $user->usertypeID = $role->ci3_usertype_id;
            }
        }

        return $user;
    }
}
