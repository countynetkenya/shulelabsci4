<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SystemAdmin Model.
 *
 * Handles system admin operations
 * Compatible with CI3 database schema
 */
class SystemAdminModel extends Model
{
    protected $table = 'systemadmin';

    protected $primaryKey = 'systemadminID';

    protected $returnType = 'object';

    protected $allowedFields = ['name', 'username', 'password', 'email', 'photo', 'usertypeID', 'schoolID', 'active'];

    /**
     * Check if user is super admin.
     *
     * @param int $usertypeID
     * @param int $loginuserID
     * @return bool
     */
    public function isSuperAdmin(int $usertypeID, int $loginuserID = 0): bool
    {
        return $usertypeID === 0 || ($usertypeID === 1 && $loginuserID === 1);
    }
}
