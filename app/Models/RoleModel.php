<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RoleModel - Manages user roles.
 */
class RoleModel extends Model
{
    protected $table = 'ci4_roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'role_name',
        'role_slug',
        'ci3_usertype_id',
        'description',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
