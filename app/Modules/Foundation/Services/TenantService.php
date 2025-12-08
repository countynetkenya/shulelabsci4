<?php

namespace Modules\Foundation\Services;

use App\Models\SchoolModel;
use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class TenantService
{
    protected $schoolModel;
    protected $userModel;

    public function __construct()
    {
        $this->schoolModel = new SchoolModel();
        $this->userModel   = new UserModel();
    }

    /**
     * Get all tenants (schools).
     */
    public function getAllTenants()
    {
        return $this->schoolModel->findAll();
    }

    /**
     * Create a new tenant (school) and its initial admin user.
     *
     * @param array $data
     * @return array|bool Result array with 'success' and 'message' or 'errors'
     */
    public function createTenant(array $data)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Create School
            $schoolData = [
                'school_name' => $data['name'],
                'school_code' => $data['code'],
                'email'       => $data['admin_email'],
                'is_active'   => 1,
                // Default values
                'country'     => 'Kenya',
                'currency'    => 'KES',
            ];

            if (!$this->schoolModel->insert($schoolData)) {
                return ['success' => false, 'errors' => $this->schoolModel->errors()];
            }

            $schoolId = $this->schoolModel->getInsertID();

            // 2. Create Admin User
            // Note: Password generation should be handled securely. For now, using a default or random.
            $tempPassword = bin2hex(random_bytes(4)); // 8 char random password
            
            $userData = [
                'email'       => $data['admin_email'],
                'username'    => explode('@', $data['admin_email'])[0],
                'password'    => $tempPassword, 
                'first_name'  => 'Admin',
                'last_name'   => 'User',
                'school_id'   => $schoolId,
                'is_active'   => 1,
            ];

            // Check if user exists globally? 
            // For now, assuming email is unique per system or handled by model.
            if (!$this->userModel->insert($userData)) {
                // Rollback handled by transComplete/transStatus? 
                // No, insert() failure doesn't throw exception by default unless configured.
                // But we are in a transaction.
                $db->transRollback();
                return ['success' => false, 'errors' => $this->userModel->errors()];
            }
            
            $userId = $this->userModel->getInsertID();

            // 3. Assign Role
            // Get role ID for 'admin' (assuming 'admin' slug exists)
            $role = $db->table('roles')->where('role_slug', 'admin')->get()->getRow();
            
            if ($role) {
                $db->table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $role->id,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                // Fallback or log error if role doesn't exist
                // For now, we proceed but maybe log a warning
                log_message('error', "Role 'admin' not found when creating tenant for user ID {$userId}");
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return ['success' => false, 'message' => 'Transaction failed.'];
            }

            return [
                'success' => true, 
                'message' => "School created successfully. Admin Password: {$tempPassword}",
                'school_id' => $schoolId
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
