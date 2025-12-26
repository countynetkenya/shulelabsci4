<?php

namespace Modules\Database\Models;

use CodeIgniter\Model;

/**
 * DatabaseBackupModel - Handles database backup records.
 */
class DatabaseBackupModel extends Model
{
    protected $table = 'db_backups';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'school_id',
        'backup_id',
        'name',
        'path',
        'size',
        'status',
        'type',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'school_id' => 'required|integer',
        'backup_id' => 'required|max_length[100]',
        'name' => 'required|max_length[200]',
        'path' => 'required|max_length[500]',
        'size' => 'permit_empty|integer',
        'status' => 'permit_empty|in_list[pending,in_progress,completed,failed]',
        'type' => 'permit_empty|in_list[full,incremental,differential]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;
}
