<?php

namespace App\Models;

/**
 * ThreadAnnouncementModel - School announcements.
 */
class ThreadAnnouncementModel extends TenantModel
{
    protected $table = 'thread_announcements';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'school_id',
        'author_id',
        'title',
        'content',
        'target_audience',
        'published_at',
        'is_active',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';
}
