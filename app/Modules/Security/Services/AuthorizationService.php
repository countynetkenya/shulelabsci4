<?php

namespace App\Modules\Security\Services;

use App\Modules\Security\Models\RoleModel;
use App\Modules\Security\Models\PermissionModel;

/**
 * AuthorizationService - Handles RBAC authorization checks.
 */
class AuthorizationService
{
    private RoleModel $roleModel;
    private PermissionModel $permissionModel;
    private array $userPermissions = [];

    public function __construct(?RoleModel $roleModel = null, ?PermissionModel $permissionModel = null)
    {
        $this->roleModel = $roleModel ?? new RoleModel();
        $this->permissionModel = $permissionModel ?? new PermissionModel();
    }

    /**
     * Check if a user has a specific permission.
     */
    public function hasPermission(int $userId, string $permissionSlug, ?int $schoolId = null): bool
    {
        $permissions = $this->getUserPermissions($userId, $schoolId);
        return in_array($permissionSlug, $permissions, true);
    }

    /**
     * Check if a user has any of the given permissions.
     */
    public function hasAnyPermission(int $userId, array $permissionSlugs, ?int $schoolId = null): bool
    {
        $permissions = $this->getUserPermissions($userId, $schoolId);
        return !empty(array_intersect($permissionSlugs, $permissions));
    }

    /**
     * Check if a user has all of the given permissions.
     */
    public function hasAllPermissions(int $userId, array $permissionSlugs, ?int $schoolId = null): bool
    {
        $permissions = $this->getUserPermissions($userId, $schoolId);
        return empty(array_diff($permissionSlugs, $permissions));
    }

    /**
     * Check if a user has a specific role.
     */
    public function hasRole(int $userId, string $roleSlug, ?int $schoolId = null): bool
    {
        $roles = $this->getUserRoles($userId, $schoolId);
        return in_array($roleSlug, array_column($roles, 'slug'), true);
    }

    /**
     * Get all permissions for a user.
     */
    public function getUserPermissions(int $userId, ?int $schoolId = null): array
    {
        $cacheKey = "{$userId}_{$schoolId}";
        if (isset($this->userPermissions[$cacheKey])) {
            return $this->userPermissions[$cacheKey];
        }

        $db = \Config\Database::connect();
        $permissions = $db->table('permissions')
            ->select('permissions.slug')
            ->join('role_permissions', 'role_permissions.permission_id = permissions.id')
            ->join('user_roles', 'user_roles.role_id = role_permissions.role_id')
            ->where('user_roles.user_id', $userId)
            ->groupStart()
            ->where('user_roles.school_id', $schoolId)
            ->orWhere('user_roles.school_id IS NULL')
            ->groupEnd()
            ->groupStart()
            ->where('user_roles.expires_at IS NULL')
            ->orWhere('user_roles.expires_at >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->get()
            ->getResultArray();

        $this->userPermissions[$cacheKey] = array_column($permissions, 'slug');
        return $this->userPermissions[$cacheKey];
    }

    /**
     * Get all roles for a user.
     */
    public function getUserRoles(int $userId, ?int $schoolId = null): array
    {
        $db = \Config\Database::connect();
        return $db->table('roles')
            ->select('roles.*')
            ->join('user_roles', 'user_roles.role_id = roles.id')
            ->where('user_roles.user_id', $userId)
            ->groupStart()
            ->where('user_roles.school_id', $schoolId)
            ->orWhere('user_roles.school_id IS NULL')
            ->groupEnd()
            ->get()
            ->getResultArray();
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(int $userId, int $roleId, int $schoolId, ?int $assignedBy = null, ?string $expiresAt = null): bool
    {
        $db = \Config\Database::connect();
        return $db->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'school_id' => $schoolId,
            'assigned_by' => $assignedBy,
            'assigned_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(int $userId, int $roleId, int $schoolId): bool
    {
        $db = \Config\Database::connect();
        return $db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('school_id', $schoolId)
            ->delete();
    }

    /**
     * Clear cached permissions for a user.
     */
    public function clearCache(int $userId, ?int $schoolId = null): void
    {
        $cacheKey = "{$userId}_{$schoolId}";
        unset($this->userPermissions[$cacheKey]);
    }
}
