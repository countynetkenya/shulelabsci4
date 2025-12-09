<?php

namespace Modules\Foundation\Services;

use App\Models\RoleModel;
use App\Models\UserModel;

class UsersService
{
    protected $userModel;

    protected $roleModel;

    protected $db;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->db = \Config\Database::connect();
    }

    public function getAllUsers()
    {
        $users = $this->userModel->findAll();

        $results = [];
        foreach ($users as $user) {
            // Get Role
            $role = $this->db->table('user_roles')
                        ->join('roles', 'roles.id = user_roles.role_id')
                        ->where('user_id', $user->id)
                        ->get()
                        ->getRowArray();

            $userArray = (array) $user;
            $userArray['role'] = $role ? $role['role_name'] : 'N/A';
            $userArray['role_id'] = $role ? $role['id'] : null;
            $userArray['status'] = $user->is_active ? 'Active' : 'Inactive';

            $results[] = $userArray;
        }

        return $results;
    }

    public function getUserById($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return null;
        }

        $role = $this->db->table('user_roles')
                    ->where('user_id', $user->id)
                    ->get()
                    ->getRowArray();

        $userArray = (array) $user;
        $userArray['role_id'] = $role ? $role['role_id'] : null;

        return $userArray;
    }

    public function createUser(array $data)
    {
        $this->db->transStart();

        // Prepare user data
        $userData = [
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'is_active' => 1,
            // 'schoolID' => 1, // Defaulting to 1 for now as per context
        ];

        $userId = $this->userModel->insert($userData);

        if ($userId) {
            // Assign Role
            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $data['role_id'],
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function updateUser($id, array $data)
    {
        $this->db->transStart();

        $userData = [
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $userData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $userData);

        // Update Role
        // First check if exists
        $exists = $this->db->table('user_roles')->where('user_id', $id)->countAllResults();
        if ($exists) {
            $this->db->table('user_roles')->where('user_id', $id)->update(['role_id' => $data['role_id']]);
        } else {
            $this->db->table('user_roles')->insert([
                'user_id' => $id,
                'role_id' => $data['role_id'],
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function deleteUser($id)
    {
        $this->db->transStart();
        $this->db->table('user_roles')->where('user_id', $id)->delete();
        $this->userModel->delete($id);
        $this->db->transComplete();
        return $this->db->transStatus();
    }

    public function getAllRoles()
    {
        return $this->roleModel->findAll();
    }
}
