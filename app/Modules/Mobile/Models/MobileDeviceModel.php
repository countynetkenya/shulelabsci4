<?php

namespace Modules\Mobile\Models;

use CodeIgniter\Model;

/**
 * MobileDeviceModel - Handles mobile device records.
 */
class MobileDeviceModel extends Model
{
    protected $table = 'mobile_devices';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'device_id',
        'device_name',
        'device_type',
        'os_version',
        'app_version',
        'is_active',
        'last_active_at',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'device_id' => 'required|max_length[255]',
        'device_type' => 'required|in_list[ios,android,web]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;
}
