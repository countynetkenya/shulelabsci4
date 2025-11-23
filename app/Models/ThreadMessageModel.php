<?php

namespace App\Models;

use App\Models\TenantModel;

/**
 * ThreadMessageModel - User messages.
 */
class ThreadMessageModel extends TenantModel
{
    protected $table = 'thread_messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'school_id',
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'sent_at',
        'is_read',
        'read_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
