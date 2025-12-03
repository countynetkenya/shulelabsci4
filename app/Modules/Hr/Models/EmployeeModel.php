<?php

namespace Modules\Hr\Models;

use App\Models\TenantModel;

class EmployeeModel extends TenantModel
{
    protected $table            = 'employees';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_id',
        'user_id',
        'employee_number',
        'department_id',
        'designation_id',
        'reports_to',
        'employment_type',
        'join_date',
        'confirmation_date',
        'end_date',
        'status',
        'basic_salary',
        'bank_name',
        'bank_account',
        'tax_id',
        'social_security_id',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'school_id'       => 'required|integer',
        'user_id'         => 'required|integer',
        'employee_number' => 'required|max_length[50]',
        'employment_type' => 'required|in_list[permanent,contract,part_time,intern,probation]',
        'join_date'       => 'required|valid_date',
        'status'          => 'required|in_list[active,on_leave,suspended,terminated,resigned]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
