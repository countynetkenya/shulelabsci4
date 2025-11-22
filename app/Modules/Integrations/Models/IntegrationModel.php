<?php

namespace Modules\Integrations\Models;

use CodeIgniter\Model;

/**
 * Model for integration configurations.
 */
class IntegrationModel extends Model
{
    protected $table         = 'ci4_integration_integrations';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'name',
        'type',
        'adapter_class',
        'config_json',
        'is_active',
        'tenant_id',
    ];

    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $returnType = 'array';

    protected $validationRules = [
        'name'          => 'required|max_length[100]|is_unique[ci4_integration_integrations.name,id,{id}]',
        'type'          => 'required|max_length[50]',
        'adapter_class' => 'required|max_length[255]',
        'is_active'     => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'  => 'Integration name is required',
            'is_unique' => 'An integration with this name already exists',
        ],
    ];
}
