<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User Model.
 *
 * Handles user authentication using CI4-native user tables
 * Uses users table exclusively for authentication
 */
class UserModel extends Model
{
    protected $table = 'users';

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
        'is_active',
    ];

    /**
     * Find user by username (active users only).
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
     * Used for detailed signin error messages.
     *
     * @param string $username
     * @return object|null
     */
    public function findByUsernameAnyStatus(string $username): ?object
    {
        return $this->where('username', $username)
                    ->orWhere('email', $username)
                    ->first();
    }

    /**
     * Get user with their roles.
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
        $roles = $this->db->table('user_roles ur')
            ->select('r.id, r.role_name, r.role_slug, r.description')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->getResult();

        $user->roles = $roles;

        if (!empty($roles)) {
            // keep primary role reference without CI3 fields
            $user->primaryRole = $roles[0];
        }

        return $user;
    }

    /**
     * Get user's primary role (first assigned role).
     *
     * @param int $userId
     * @return object|null
     */
    public function getUserPrimaryRole(int $userId): ?object
    {
        return $this->db->table('user_roles ur')
            ->select('r.*')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->orderBy('ur.id', 'ASC')
            ->limit(1)
            ->get()
            ->getRow();
    }

    /**
     * Get all users with their roles for admin panel.
     *
     * @return array
     */
    public function getUsersWithRoles(): array
    {
        $users = $this->select('users.*, r.role_name, r.id as role_id')
            ->join('user_roles ur', 'ur.user_id = users.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->findAll();

        return $users;
    }

    /**
     * Check if user has a specific role.
     *
     * @param int $userId
     * @param string $roleSlug
     * @return bool
     */
    public function hasRole(int $userId, string $roleSlug): bool
    {
        $count = $this->db->table('user_roles ur')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->where('r.role_slug', $roleSlug)
            ->countAllResults();

        return $count > 0;
    }

    // Removed deprecated CI3-compatible signin method
}
