<?php

namespace App\Modules\Scheduler\Models;

use CodeIgniter\Model;

class SchedulerEventModel extends Model
{
    protected $table = 'scheduler_events';
    protected $primaryKey = 'id';
    protected $allowedFields = ['school_id', 'title', 'start_time', 'end_time', 'description', 'location'];
    protected $useTimestamps = true;
}
