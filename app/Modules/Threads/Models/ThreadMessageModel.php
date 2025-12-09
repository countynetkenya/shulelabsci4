<?php

namespace App\Modules\Threads\Models;

use CodeIgniter\Model;

class ThreadMessageModel extends Model
{
    protected $table = 'thread_messages';

    protected $primaryKey = 'id';

    protected $allowedFields = ['school_id', 'sender_id', 'recipient_id', 'subject', 'body', 'is_read'];

    protected $useTimestamps = true;
}
