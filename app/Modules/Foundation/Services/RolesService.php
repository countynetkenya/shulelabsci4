<?php

namespace Modules\Foundation\Services;

use App\Models\RoleModel;

class RolesService
{
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    public function getAllRoles()
    {
        $roles = $this->roleModel->findAll();
        
        // Get user counts
        $db = \Config\Database::connect();
        foreach ($roles as &$role) {
            $count = $db->table('user_roles')
                        ->where('role_id', $role['id'])
                        ->countAllResults();
            $role['users_count'] = $count;
        }
        
        return $roles;
    }

    public function getRoleById($id)
    {
        return $this->roleModel->find($id);
    }

    public function createRole(array $data)
    {
        // Default ci3_usertype_id to 0 or something safe if not provided
        if (!isset($data['ci3_usertype_id'])) {
            $data['ci3_usertype_id'] = 999; // Custom role
        }
        
        return $this->roleModel->insert($data);
    }

    public function updateRole($id, array $data)
    {
        return $this->roleModel->update($id, $data);
    }

    public function deleteRole($id)
    {
        return $this->roleModel->delete($id);
    }
}
