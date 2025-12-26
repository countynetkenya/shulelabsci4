<?php

namespace Modules\Orchestration\Models;

use CodeIgniter\Model;

class WorkflowModel extends Model
{
    protected $table = 'workflows';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $allowedFields = ['school_id', 'workflow_id', 'name', 'description', 'steps', 'status', 'current_step', 'total_steps', 'started_at', 'completed_at', 'error_message', 'created_by'];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'school_id' => 'required|integer',
        'workflow_id' => 'required|max_length[100]',
        'name' => 'required|max_length[200]',
        'status' => 'permit_empty|in_list[pending,running,completed,failed,paused]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;
}
