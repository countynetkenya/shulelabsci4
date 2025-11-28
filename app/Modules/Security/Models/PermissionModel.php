<?php

namespace App\Modules\Security\Models;

use CodeIgniter\Model;

/**
 * PermissionModel - Manages system permissions.
 */
class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'name', 'slug', 'module', 'description',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Get permissions grouped by module.
     */
    public function getGroupedByModule(): array
    {
        $permissions = $this->orderBy('module')->orderBy('name')->findAll();
        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['module']][] = $permission;
        }
        return $grouped;
    }

    /**
     * Get permissions for a role.
     */
    public function getForRole(int $roleId): array
    {
        return $this->select('permissions.*')
            ->join('role_permissions', 'role_permissions.permission_id = permissions.id')
            ->where('role_permissions.role_id', $roleId)
            ->findAll();
    }

    /**
     * Get permission by slug.
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }
}
